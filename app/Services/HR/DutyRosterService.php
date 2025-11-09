<?php

namespace App\Services\HR;

use App\Repositories\HR\Interfaces\DutyRosterRepositoryInterface;
use App\Repositories\HR\Interfaces\EmployeeRepositoryInterface;
use App\Repositories\HR\Interfaces\ShiftRepositoryInterface;
use App\Models\RosterImport;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        // returns collection; controller will shape for UI
        return $this->repo->listForWeek($propertyCode, $weekStartDate);
    }

    public function create(string $propertyCode, array $data)
    {
        $data['property_code'] = $propertyCode;
        // validate employee belongs to property
        $emp = $this->employeeRepo->findByEmployeeCode($propertyCode, $data['employee_code'] ?? '');
        if (!$emp) {
            throw ValidationException::withMessages(['employee_code' => ['Employee not found for property.']]);
        }
        $data['employee_id'] = $emp->id;
        unset($data['employee_code']);
        return $this->repo->upsertForEmployeeDate($propertyCode, $data['employee_id'], $data['roster_date'], $data);
    }

    /**
     * Process bulk upload file.
     * Expected format:
     * Row: Emp. Code | date columns in YYYY-MM-DD as header -> cell contains shift code (or empty)
     *
     * Returns summary:
     *  ['stored_path' => ..., 'total_rows' => X, 'processed' => Y, 'errors' => [ {row:2, employee_code:'', messages:[]}, ... ] ]
     */
    public function processBulkUpload(string $propertyCode, $uploadedFile, ?int $uploadedBy = null): array
    {
        // save file
        $filename = 'roster_uploads/'.date('Ymd_His_').Str::random(6).'.'.$uploadedFile->getClientOriginalExtension();
        $path = $uploadedFile->storeAs('public', $filename);
        $storagePath = storage_path('app/'.$path);

        // load spreadsheet
        $spreadsheet = IOFactory::load($storagePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true); // preserve columns with letters

        if (count($rows) < 2) {
            return ['stored_path'=>$path, 'total_rows'=>0, 'processed'=>0, 'errors'=>[['row'=>0,'messages'=>['Empty or invalid file']]]];
        }

        // header detection: assume first row contains headers: first col "Emp. Code" and other headers are dates (YYYY-MM-DD)
        $header = $rows[1];
        $colMap = [];
        $dateCols = [];
        $firstColLetter = array_key_first($header);

        // map columns
        foreach ($header as $colLetter => $colValue) {
            $val = trim((string)$colValue);
            if ($colLetter == $firstColLetter) {
                // first column must be Emp. Code or Employee Code
                if (!in_array(strtolower($val), ['emp. code','emp code','employee code','employee_code','employee'])) {
                    // allow small variations but warn if not found
                    // we'll still try to process but return error
                }
                $colMap['employee'] = $colLetter;
            } else {
                // treat header as date; only accept YYYY-MM-DD
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                    $dateCols[$colLetter] = $val;
                }
            }
        }

        $total = 0;
        $processed = 0;
        $errors = [];

        // pre-cache employees by code for this property to speed up lookups
        // we expect employeeRepo has method findAllByProperty to fetch mapping; if not, fetch per row (safer fallback)
        $employeesByCode = [];
        if (method_exists($this->employeeRepo, 'getAllByProperty')) {
            $ems = $this->employeeRepo->getAllByProperty($propertyCode);
            foreach ($ems as $e) $employeesByCode[strtoupper(trim($e->employee_code))] = $e;
        }

        // iterate rows starting from row 2
        foreach ($rows as $rIndex => $row) {
            if ($rIndex === 1) continue; // header
            $total++;
            $empCode = isset($row[$colMap['employee']]) ? trim($row[$colMap['employee']]) : null;
            if (!$empCode) {
                $errors[] = ['row' => $rIndex, 'employee_code' => null, 'messages' => ['Employee code empty']];
                continue;
            }

            $empKey = strtoupper(trim($empCode));
            $employee = $employeesByCode[$empKey] ?? null;
            if (!$employee) {
                // fallback: try repo lookup by code (case-insensitive)
                $employee = $this->employeeRepo->findByEmployeeCode($propertyCode, $empCode);
            }

            if (!$employee) {
                $errors[] = ['row' => $rIndex, 'employee_code' => $empCode, 'messages' => ['Employee code not found in this property']];
                // note: as requested, continue processing other rows
                continue;
            }

            // for each date column, if there is a shift code in cell, map shift_code -> shift_id
            foreach ($dateCols as $colLetter => $dateStr) {
                $cellVal = isset($row[$colLetter]) ? trim((string)$row[$colLetter]) : null;
                if (!$cellVal) continue; // empty cell: no assignment
                $shiftCode = trim((string)$cellVal);
                // find shift by code for this property
                $shift = $this->shiftRepo->findByCode($propertyCode, $shiftCode);
                if (!$shift) {
                    // record error for this row/date but continue other date columns
                    $errors[] = [
                        'row' => $rIndex,
                        'employee_code' => $empCode,
                        'date' => $dateStr,
                        'shift_code' => $shiftCode,
                        'messages' => ["Shift code '{$shiftCode}' not found for property"]
                    ];
                    continue;
                }

                // build roster data
                $rosterData = [
                    'shift_id' => $shift->id,
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                ];

                // upsert for employee + date
                $this->repo->upsertForEmployeeDate($propertyCode, $employee->id, $dateStr, $rosterData);
                $processed++;
            }
        }

        // save import summary (optional)
        $import = \App\Models\RosterImport::create([
            'property_code' => $propertyCode,
            'file_path' => $path,
            'uploaded_by' => $uploadedBy,
            'total_rows' => $total,
            'processed_count' => $processed,
            'error_count' => count($errors),
            'errors' => $errors,
        ]);

        return [
            'stored_path' => $path,
            'total_rows' => $total,
            'processed' => $processed,
            'errors' => $errors,
            'import_id' => $import->id,
        ];
    }
}
