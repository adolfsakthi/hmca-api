<?php

namespace App\Repositories\HR\Interfaces;

interface LeaveApprovalRepositoryInterface
{
    public function create(array $data);
    public function listByLeave(int $leaveId);
}
