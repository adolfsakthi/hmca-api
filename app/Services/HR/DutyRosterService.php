<?php

namespace App\Services\HR;

use App\Repositories\HR\Interfaces\DutyRosterRepositoryInterface;
use App\Repositories\HR\Interfaces\EmployeeRepositoryInterface;
use App\Repositories\HR\Interfaces\ShiftRepositoryInterface;
use App\Models\HR\RosterImport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
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
        // must contain employee_code or employee_id
        if (isset($data['employee_code'])) {
            $employee = $this->employeeRepo->findByEmployeeCode($propertyCode, $data['employee_code']);
            if (!$employee) return null;
            $data['employee_id'] = $employee->id;
            unset($data['employee_code']);
        }

        $data['property_code'] = $propertyCode;
        return $this->repo->upsertForEmployeeDate(
            $propertyCode,
            $data['employee_id'],
            $data['roster_date'],
            $data
        );
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
     * ✅ Process Bulk Roster Upload (fixed version)
     * - Uses Storage::disk('public') for file saving
     * - Ensures file exists before processing
     * - Validates employee codes before inserting
     * - Records invalid employee and shift-code errors
     */
    public function processBulkUpload(string $propertyCode, $uploadedFile, ?int $uploadedBy = null): array
    {
        // 1️⃣ Store file safely
        $folder = 'roster_uploads';
        $ext = $uploadedFile->getClientOriginalExtension() ?: 'xlsx';
        $filename = date('Ymd_His_') . Str::random(6) . '.' . $ext;

        $storedPath = Storage::disk('public')->putFileAs($folder, $uploadedFile, $filename);
        $absolutePath = Storage::disk('public')->path($storedPath);

        // 2️⃣ Verify file existence
        if (!file_exists($absolutePath)) {
            return [
                'success' => false,
                'message' => "File not found after upload at: {$absolutePath}",
                'total_rows' => 0,
                'processed' => 0,
                'invalid_employees' => [],
                'cell_errors' => [['message' => 'File storage failed']],
            ];
        }

        // 3️⃣ Load spreadsheet
        try {
            $spreadsheet = IOFactory::load($absolutePath);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to read Excel file: ' . $e->getMessage(),
                'total_rows' => 0,
                'processed' => 0,
                'invalid_employees' => [],
                'cell_errors' => [['message' => $e->getMessage()]],
            ];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        if (count($rows) < 2) {
            return [
                'success' => false,
                'message' => 'Empty or invalid Excel file.',
                'total_rows' => 0,
                'processed' => 0,
                'invalid_employees' => [],
                'cell_errors' => [['message' => 'No rows found']],
            ];
        }

        // 4️⃣ Parse header row
        $header = $rows[1];
        $firstCol = array_key_first($header);
        $dateCols = [];
        foreach ($header as $colLetter => $value) {
            if ($colLetter === $firstCol) continue;
            $val = trim((string)$value);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
                $dateCols[$colLetter] = $val;
            }
        }

        // 5️⃣ Pre-fetch employees for this property
        $empCodes = [];
        foreach ($rows as $idx => $row) {
            if ($idx === 1) continue;
            $code = isset($row[$firstCol]) ? trim((string)$row[$firstCol]) : null;
            if ($code) $empCodes[strtoupper($code)] = true;
        }

        $employeesByCode = [];
        if (method_exists($this->employeeRepo, 'getAllByProperty')) {
            $emps = $this->employeeRepo->getAllByProperty($propertyCode);
            foreach ($emps as $e) {
                $employeesByCode[strtoupper(trim($e->employee_code))] = $e;
            }
        }

        // 6️⃣ Identify invalid employees
        $invalidEmployees = [];
        $invalidMap = [];
        foreach ($empCodes as $code => $val) {
            if (!isset($employeesByCode[$code])) {
                $invalidEmployees[] = [
                    'employee_code' => $code,
                    'message' => 'Employee not found in this property',
                ];
                $invalidMap[$code] = true;
            }
        }

        // 7️⃣ Process valid rows
        $total = 0;
        $processed = 0;
        $cellErrors = [];

        foreach ($rows as $rIdx => $row) {
            if ($rIdx === 1) continue;
            $total++;

            $empCodeRaw = trim((string)($row[$firstCol] ?? ''));
            if (!$empCodeRaw) continue;
            $empKey = strtoupper($empCodeRaw);

            if (isset($invalidMap[$empKey])) continue;

            $employee = $employeesByCode[$empKey] ?? null;
            if (!$employee) {
                $cellErrors[] = [
                    'row' => $rIdx,
                    'employee_code' => $empCodeRaw,
                    'messages' => ['Employee not found'],
                ];
                continue;
            }

            // process each date cell
            foreach ($dateCols as $col => $date) {
                $shiftCode = trim((string)($row[$col] ?? ''));
                if (!$shiftCode) continue;

                $shift = $this->shiftRepo->findByCode($propertyCode, $shiftCode);
                if (!$shift) {
                    $cellErrors[] = [
                        'row' => $rIdx,
                        'employee_code' => $empCodeRaw,
                        'date' => $date,
                        'shift_code' => $shiftCode,
                        'messages' => ["Shift code '{$shiftCode}' not found"],
                    ];
                    continue;
                }

                $data = [
                    'shift_id' => $shift->id,
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                ];

                $this->repo->upsertForEmployeeDate($propertyCode, $employee->id, $date, $data);
                $processed++;
            }
        }

        // 8️⃣ Save import summary (optional)
        $import = null;
        if (class_exists(RosterImport::class)) {
            $import = RosterImport::create([
                'property_code' => $propertyCode,
                'file_path' => $storedPath,
                'uploaded_by' => $uploadedBy,
                'total_rows' => $total,
                'processed_count' => $processed,
                'error_count' => count($invalidEmployees) + count($cellErrors),
                'errors' => [
                    'invalid_employees' => $invalidEmployees,
                    'cell_errors' => $cellErrors,
                ],
            ]);
        }

        // 9️⃣ Return summary
        return [
            'success' => true,
            'stored_as' => $storedPath,
            'message' => 'Bulk upload completed.',
            'total_rows' => $total,
            'processed' => $processed,
            'invalid_employees' => $invalidEmployees,
            'cell_errors' => $cellErrors,
            'import_id' => $import ? $import->id : null,
        ];
    }
}
