<?php

namespace App\Services\HR;

use App\Repositories\HR\Interfaces\EmployeeRepositoryInterface;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeService
{
    protected EmployeeRepositoryInterface $repo;

    public function __construct(EmployeeRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function list(string $propertyCode, ?string $search, int $perPage = 15)
    {
        return $this->repo->paginateByProperty($propertyCode, $search, $perPage);
    }

    public function get(string $propertyCode, int $id)
    {
        return $this->repo->findByIdAndProperty($id, $propertyCode);
    }

    public function create(string $propertyCode, array $data)
    {
        // Inject property code (never trust client)
        $data['property_code'] = $propertyCode;

        // Basic validation
        if (empty($data['employee_code'])) {
            throw ValidationException::withMessages([
                'employee_code' => ['Employee code is required.'],
            ]);
        }

        // Unique employee_code per property
        if ($this->repo->existsByCode($propertyCode, $data['employee_code'])) {
            throw ValidationException::withMessages([
                'employee_code' => ['Employee code already exists for this property.'],
            ]);
        }

        return $this->repo->create($data);
    }

    public function update(string $propertyCode, int $id, array $data)
    {
        if (isset($data['employee_code'])) {
            if ($this->repo->existsByCode($propertyCode, $data['employee_code'], $id)) {
                throw ValidationException::withMessages([
                    'employee_code' => ['Employee code already exists for this property.'],
                ]);
            }
        }

        return $this->repo->update($id, $propertyCode, $data);
    }

    public function delete(string $propertyCode, int $id): bool
    {
        return $this->repo->delete($id, $propertyCode);
    }

    /**
     * ✅ Bulk Upload Employees (Excel/CSV)
     * - Stores uploaded file in public/hrms_employee_bulk
     * - Reads data using PhpSpreadsheet
     * - Maps headers automatically (case-insensitive)
     * - Calls create() for each valid row
     * - Collects errors per row
     */
    public function handleBulkUpload(string $propertyCode, $uploadedFile): array
    {
        // 1️⃣ Store uploaded file safely
        $ext = $uploadedFile->getClientOriginalExtension() ?: 'xlsx';
        $filename = date('Ymd_His') . '_' . Str::random(10) . '.' . $ext;
        $folder = 'hrms_employee_bulk';
        $storedPath = Storage::disk('public')->putFileAs($folder, $uploadedFile, $filename);
        $absolutePath = Storage::disk('public')->path($storedPath);

        // Ensure file exists
        if (!file_exists($absolutePath)) {
            return [
                'success' => false,
                'message' => "File not found at: {$absolutePath}",
                'inserted' => 0,
                'errors' => [],
            ];
        }

        // 2️⃣ Load Excel
        try {
            $spreadsheet = IOFactory::load($absolutePath);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Unable to read Excel file: ' . $e->getMessage(),
                'inserted' => 0,
                'errors' => [],
            ];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return [
                'success' => false,
                'message' => 'No data found in file. Please check header and format.',
                'inserted' => 0,
                'errors' => [['row' => 0, 'errors' => ['Empty or invalid file']]],
            ];
        }

        // 3️⃣ Header Mapping (Improved)
        $headerRow = $rows[1];
        $knownHeaders = [
            'firstname' => 'first_name',
            'first name' => 'first_name',
            'lastname' => 'last_name',
            'last name' => 'last_name',
            'email' => 'email',
            'employeecode' => 'employee_code',
            'employee code' => 'employee_code',
            'empcode' => 'employee_code',
            'department' => 'department',
            'designation' => 'designation',
            'shiftstarttime' => 'shift_start_time',
            'shift start time' => 'shift_start_time',
            'shiftendtime' => 'shift_end_time',
            'shift end time' => 'shift_end_time',
            'doj' => 'date_of_joining',
            'dateofjoining' => 'date_of_joining',
            'date of joining' => 'date_of_joining',
            'outlet' => 'outlet',
        ];

        $colToField = [];
        foreach ($headerRow as $colLetter => $value) {
            $normalized = strtolower(trim((string)$value));
            $normalized = str_replace([' ', '_'], '', $normalized); // remove spaces and underscores

            if (isset($knownHeaders[$normalized])) {
                $colToField[$colLetter] = $knownHeaders[$normalized];
            }
        }

        // fallback if employee_code missing
        if (!in_array('employee_code', $colToField)) {
            $firstCol = array_key_first($headerRow);
            $colToField[$firstCol] = 'employee_code';
        }


        // 4️⃣ Process Rows
        $inserted = 0;
        $errors = [];

        foreach ($rows as $rIndex => $row) {
            if ($rIndex === 1) continue; // skip header

            $data = [];
            foreach ($colToField as $colLetter => $field) {
                $data[$field] = trim((string)($row[$colLetter] ?? ''));
            }

            if (empty($data['employee_code'])) {
                $errors[] = [
                    'row' => $rIndex,
                    'employee_code' => null,
                    'errors' => ['Employee code missing.']
                ];
                continue;
            }

            try {
                $this->create($propertyCode, $data);
                $inserted++;
            } catch (ValidationException $ve) {
                $errors[] = [
                    'row' => $rIndex,
                    'employee_code' => $data['employee_code'],
                    'errors' => $ve->errors(),
                ];
            } catch (\Throwable $e) {
                $errors[] = [
                    'row' => $rIndex,
                    'employee_code' => $data['employee_code'],
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        // 5️⃣ Return Response Summary
        return [
            'success' => true,
            'stored_as' => $storedPath,
            'message' => 'Bulk upload completed.',
            'inserted' => $inserted,
            'errors' => $errors,
        ];
    }
}
