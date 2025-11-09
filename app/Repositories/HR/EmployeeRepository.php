<?php

namespace App\Repositories\HR;

use App\Models\HR\Employee;
use App\Repositories\HR\Interfaces\EmployeeRepositoryInterface;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function paginateByProperty(string $propertyCode, ?string $search, int $perPage = 15)
    {
        $q = Employee::where('property_code', $propertyCode);

        if ($search) {
            $q->where(function ($qr) use ($search) {
                $qr->where('first_name', 'like', "%$search%")
                   ->orWhere('last_name', 'like', "%$search%")
                   ->orWhere('email', 'like', "%$search%")
                   ->orWhere('employee_code', 'like', "%$search%");
            });
        }

        return $q->orderBy('first_name')->paginate($perPage);
    }

    public function findByIdAndProperty(int $id, string $propertyCode)
    {
        return Employee::where('property_code', $propertyCode)->find($id);
    }

    public function create(array $data)
    {
        return Employee::create($data);
    }

    public function update(int $id, string $propertyCode, array $data)
    {
        $emp = $this->findByIdAndProperty($id, $propertyCode);
        if (!$emp) {
            return null;
        }
        $emp->update($data);
        return $emp;
    }

    public function delete(int $id, string $propertyCode): bool
    {
        $emp = $this->findByIdAndProperty($id, $propertyCode);
        if (!$emp) {
            return false;
        }
        $emp->delete();
        return true;
    }

    public function existsByCode(string $propertyCode, string $employeeCode, ?int $ignoreId = null): bool
    {
        $q = Employee::where('property_code', $propertyCode)
            ->where('employee_code', $employeeCode);

        if ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        }

        return $q->exists();
    }
}
