<?php

namespace App\Services\HR;

use App\Repositories\HR\Interfaces\EmployeeRepositoryInterface;
use Illuminate\Validation\ValidationException;

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
        // inject property_code (never trust client)
        $data['property_code'] = $propertyCode;

        // unique employee_code check inside property scope
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

        $emp = $this->repo->update($id, $propertyCode, $data);
        return $emp;
    }

    public function delete(string $propertyCode, int $id): bool
    {
        return $this->repo->delete($id, $propertyCode);
    }

    public function handleBulkUpload(string $propertyCode, $uploadedFile): array
    {
        // minimal version:
        // 1. save file
        // 2. return success message
        // You can later parse Excel and create employees line by line.

        $path = $uploadedFile->store('hrms_employee_bulk');

        return [
            'stored_as' => $path,
            'message'   => 'File received. Processing not yet implemented.',
        ];
    }
}
