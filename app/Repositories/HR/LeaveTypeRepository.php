<?php

namespace App\Repositories\HR;

use App\Repositories\HR\Interfaces\LeaveTypeRepositoryInterface;
use App\Models\HR\LeaveType;

class LeaveTypeRepository implements LeaveTypeRepositoryInterface
{
    public function create(array $data)
    {
        return LeaveType::create($data);
    }

    public function update(int $id, string $propertyCode, array $data)
    {
        $lt = LeaveType::where('id', $id)->where('property_code', $propertyCode)->first();
        if (!$lt) return null;
        $lt->update($data);
        return $lt;
    }

    public function delete(int $id, string $propertyCode): bool
    {
        $lt = LeaveType::where('id', $id)->where('property_code', $propertyCode)->first();
        if (!$lt) return false;
        $lt->delete();
        return true;
    }

    public function findByIdAndProperty(int $id, string $propertyCode)
    {
        return LeaveType::where('id', $id)->where('property_code', $propertyCode)->first();
    }

    public function listByProperty(string $propertyCode)
    {
        return LeaveType::where('property_code', $propertyCode)->orderBy('name')->get();
    }
}
