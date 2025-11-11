<?php

namespace App\Repositories\HR;

use App\Repositories\HR\Interfaces\LeaveRepositoryInterface;
use App\Models\HR\Leave;

class LeaveRepository implements LeaveRepositoryInterface
{
    public function create(array $data)
    {
        return Leave::create($data);
    }

    public function update(int $id, string $propertyCode, array $data)
    {
        $leave = Leave::where('id', $id)->where('property_code', $propertyCode)->first();
        if (!$leave) return null;
        $leave->update($data);
        return $leave;
    }

    public function delete(int $id, string $propertyCode): bool
    {
        $leave = Leave::where('id', $id)->where('property_code', $propertyCode)->first();
        if (!$leave) return false;
        $leave->delete();
        return true;
    }

    public function findByIdAndProperty(int $id, string $propertyCode)
    {
        return Leave::where('id', $id)->where('property_code', $propertyCode)
            ->with(['type','employee','deptApprover','hrApprover','approvals'])
            ->first();
    }

    public function listByProperty(string $propertyCode)
    {
        return Leave::where('property_code', $propertyCode)
            ->with(['type','employee','deptApprover','hrApprover'])
            ->orderBy('created_at','desc')
            ->get();
    }

    public function listByEmployee(int $employeeId, string $propertyCode)
    {
        return Leave::where('property_code', $propertyCode)->where('employee_id', $employeeId)
            ->with('type')
            ->orderBy('from_date','desc')->get();
    }
}
