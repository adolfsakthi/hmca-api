<?php

namespace App\Repositories\HR;

use App\Repositories\HR\Interfaces\LeaveApprovalRepositoryInterface;
use App\Models\HR\LeaveApproval;

class LeaveApprovalRepository implements LeaveApprovalRepositoryInterface
{
    public function create(array $data)
    {
        return LeaveApproval::create($data);
    }

    public function listByLeave(int $leaveId)
    {
        return LeaveApproval::where('leave_id', $leaveId)->orderBy('performed_at')->get();
    }
}
