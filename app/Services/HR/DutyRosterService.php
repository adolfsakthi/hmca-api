<?php

namespace App\Services\HR;

use App\Repositories\HR\Interfaces\DutyRosterRepositoryInterface;
use App\Repositories\HR\Interfaces\EmployeeRepositoryInterface;
use App\Repositories\HR\Interfaces\ShiftRepositoryInterface;
use App\Models\RosterImport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class DutyRosterService
{
    protected DutyRosterRepositoryInterface $repo;
    protected EmployeeRepositoryInterface $employeeRepo;
    protected ShiftRepositoryInterface $shiftRepo;

    public function __construct(
        DutyRosterRepositoryInterface $repo,
        EmployeeRepositoryInterface $employeeRepo,
        ShiftRepositoryInterface $shiftRepo
    ) {
        $this->repo = $repo;
        $this->employeeRepo = $employeeRepo;
        $this->shiftRepo = $shiftRepo;
    }

    public function listForWeek(string $propertyCode, string $weekStartDate)
    {
        return $this->repo->listForWeek($propertyCode, $weekStartDate);
    }

    public function get(string $propertyCode, int $id)
    {
        return $this->repo->findByIdAndProperty($id, $propertyCode);
    }

    public function store(string $propertyCode, array $data)
    {
        // data must contain employee_code or employee_id + roster_date
        if (isset($data['employee_code'])) {
            $employee = $this->employeeRepo->findByEmployeeCode($propertyCode, $data['employee_code']);
            if (!$employee) {
                return null;
            }
            $data['employee_id'] = $employee->id;
            unset($data['employee_code']);
        }

        $data['property_code'] = $propertyCode;
        return $this->repo->upsertForEmployeeDate($propertyCode, $data['employee_id'], $data['roster_date'], $data);
    }

    public function update(string $propertyCode, int $id, array $data)
    {
        return $this->repo->update($id, $propertyCode, $data);
    }

    public function delete(string $propertyCode, int $id): bool
    {
        return $this->repo->delete($id, $propertyCode);
    }

    /**
     * Process bulk roster upload with a pre-validation pass for employee codes.
     * Returns summary array with processed counts and error lists.
     */
    public function processBulkUpload(string $propertyCode, $uploadedFile, ?int $uploadedBy = null): array
    {
        // store file
        $ext = $uploadedFile->getClientOriginalExtension() ?: 'xlsx';
        $filename = 'roster_uploads/'.date('Ymd_His_').Str::random(6).'.'.$ext;
        $stored = $uploadedFile->storeAs('public', $filename);
        $storagePath = storage_path('app/'.$stored);

        $spreadsheet = IOFactory::load($storagePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return [
                'stored_path' => $stored,
                'total_rows' => 0,
                'processed' => 0,
                'invalid_employees' => [],
                'cell_errors' => [['message' => 'Empty or invalid file']],
            ];
        }

        // header parse
        $header = $rows[1];
        $firstCol = array_key_first($header);
        $colMap = ['employee' => $firstCol];
        $dateCols = [];
        foreach ($header as $colLetter => $colVal) {
            if ($colLetter === $firstCol) continue;
            $val = trim((string)$colVal);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                $dateCols[$colLetter] = $val;
            }
        }

        // FIRST PASS: collect employee codes
        $empCodes = [];
        $rowIndexesByCode = [];
        foreach ($rows as $rIdx => $row) {
            if ($rIdx === 1) continue;
            $empCode = isset($row[$firstCol]) ? trim((string)$row[$firstCol]) : null;
            if (!$empCode) continue;
            $key = strtoupper($empCode);
            $empCodes[$key] = $key;
            $rowIndexesByCode[$key][] = $rIdx;
        }

        // batch fetch employees for this property if possible
        $employeesByCode = [];
        if (!empty($empCodes)) {
            if (method_exists($this->employeeRepo, 'getAllByProperty')) {
                $emps = $this->employeeRepo->getAllByProperty($propertyCode);
                foreach ($emps as $e) {
                    $employeesByCode[strtoupper(trim($e->employee_code))] = $e;
                }
            } else {
                // fallback: single lookups
                foreach ($empCodes as $codeKey) {
                    $e = $this->employeeRepo->findByEmployeeCode($propertyCode, $codeKey);
                    if ($e) $employeesByCode[$codeKey] = $e;
                }
            }
        }

        // build invalid employees
        $invalidEmployees = [];
        $invalidMap = [];
        foreach ($rowIndexesByCode as $codeKey => $rowsIdx) {
            if (!isset($employeesByCode[$codeKey])) {
                $invalidEmployees[] = [
                    'employee_code' => $codeKey,
                    'rows' => $rowsIdx,
                    'message' => 'Employee code not found in this property'
                ];
                $invalidMap[$codeKey] = true;
            }
        }

        // SECOND PASS: process valid rows
        $total = 0;
        $processed = 0;
        $cellErrors = [];

        foreach ($rows as $rIdx => $row) {
            if ($rIdx === 1) continue;
            $total++;
            $empCodeRaw = isset($row[$firstCol]) ? trim((string)$row[$firstCol]) : null;
            if (!$empCodeRaw) {
                $cellErrors[] = ['row' => $rIdx, 'messages' => ['Employee code empty']];
                continue;
            }
            $empKey = strtoupper($empCodeRaw);
            if (isset($invalidMap[$empKey])) {
                // skip invalid employee rows
                continue;
            }
            $employee = $employeesByCode[$empKey] ?? $this->employeeRepo->findByEmployeeCode($propertyCode, $empCodeRaw);
            if (!$employee) {
                // defensive
                $cellErrors[] = ['row' => $rIdx, 'employee_code' => $empCodeRaw, 'messages' => ['Employee not found']];
                continue;
            }

            foreach ($dateCols as $colLetter => $dateStr) {
                $cellVal = isset($row[$colLetter]) ? trim((string)$row[$colLetter]) : null;
                if (!$cellVal) continue;
                $shiftCode = trim($cellVal);
                $shift = $this->shiftRepo->findByCode($propertyCode, $shiftCode);
                if (!$shift) {
                    $cellErrors[] = [
                        'row' => $rIdx,
                        'employee_code' => $empCodeRaw,
                        'date' => $dateStr,
                        'shift_code' => $shiftCode,
                        'messages' => ["Shift code '{$shiftCode}' not found for property"]
                    ];
                    continue;
                }

                $rosterData = [
                    'shift_id' => $shift->id,
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                ];

                $this->repo->upsertForEmployeeDate($propertyCode, $employee->id, $dateStr, $rosterData);
                $processed++;
            }
        }

        // store import summary (optional model RosterImport)
        $import = null;
        if (class_exists(\App\Models\RosterImport::class)) {
            $import = \App\Models\RosterImport::create([
                'property_code' => $propertyCode,
                'file_path' => $stored,
                'uploaded_by' => $uploadedBy,
                'total_rows' => $total,
                'processed_count' => $processed,
                'error_count' => count($invalidEmployees) + count($cellErrors),
                'errors' => ['invalid_employees' => $invalidEmployees, 'cell_errors' => $cellErrors],
            ]);
        }

        return [
            'stored_path' => $stored,
            'total_rows' => $total,
            'processed' => $processed,
            'invalid_employees' => $invalidEmployees,
            'cell_errors' => $cellErrors,
            'import_id' => $import ? $import->id : null,
        ];
    }
}
