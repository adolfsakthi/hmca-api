<?php

namespace App\Services\HR;

use App\Repositories\HR\Interfaces\LeaveRepositoryInterface;
use App\Repositories\HR\Interfaces\LeaveTypeRepositoryInterface;
use App\Repositories\HR\Interfaces\LeaveApprovalRepositoryInterface;
use Illuminate\Validation\ValidationException;
use App\Models\HR\Leave;
use App\Repositories\HR\Interfaces\EmployeeRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class LeaveService
{
    protected LeaveRepositoryInterface $repo;
    protected LeaveTypeRepositoryInterface $typeRepo;
    protected LeaveApprovalRepositoryInterface $approvalRepo;
    protected EmployeeRepositoryInterface $employeeRepo;

    public function __construct(
        LeaveRepositoryInterface $repo,
        LeaveTypeRepositoryInterface $typeRepo,
        LeaveApprovalRepositoryInterface $approvalRepo,
        EmployeeRepositoryInterface $employeeRepo
    ) {
        $this->repo = $repo;
        $this->typeRepo = $typeRepo;
        $this->approvalRepo = $approvalRepo;
        $this->employeeRepo = $employeeRepo;
    }

    public function list(string $propertyCode)
    {
        return $this->repo->listByProperty($propertyCode);
    }

    public function listByEmployee(int $employeeId, string $propertyCode)
    {
        return $this->repo->listByEmployee($employeeId, $propertyCode);
    }

    public function get(string $propertyCode, int $id)
    {
        return $this->repo->findByIdAndProperty($id, $propertyCode);
    }

    public function apply(string $propertyCode, array $data)
    {

        $protectedId = null;
        if ($data['is_approved'] === true) {
            $payload = JWTAuth::parseToken()->getPayload();
            $role = $payload->get('role');
            $email = $payload->get('email');

            if ($role === 'admin') {
                $protectedId = 'Admin';
                $data['status'] = "Approved";
            } else {
                $emp = $this->employeeRepo->getByEmail($email, $propertyCode);
                if ($emp) {
                    $protectedId = $data['employee_code'];
                    $data['status'] = 'pending';
                } else {
                    $protectedId = $role;
                    $data['status'] = "Approved";
                }
            }
        } else {
            $data['is_approved'] = false;
        }

        Log::info('data',[$data['is_approved'],$data['status']]);
        return $data;

        // $data['property_code'] = $propertyCode;

        // $lt = $this->typeRepo->findByIdAndProperty($data['leave_type_id'] ?? 0, $propertyCode);
        // if (!$lt) {
        //     throw ValidationException::withMessages(['leave_type_id' => ['Invalid leave type for this property.']]);
        // }

        // if (empty($data['from_date']) || empty($data['to_date'])) {
        //     throw ValidationException::withMessages(['date' => ['From and To date are required.']]);
        // }



        // // create leave
        // $leave = $this->repo->create($data);

        // // audit row
        // $this->approvalRepo->create([
        //     'property_code' => $propertyCode,
        //     'leave_id' => $leave->id,
        //     'action' => 'applied',
        //     'performed_by' => $protectedId,
        //     'performed_at' => now(),
        //     'remarks' => $data['remarks'] ?? null,
        // ]);

        // return $leave;
    }

    public function update(string $propertyCode, int $id, array $data)
    {
        return $this->repo->update($id, $propertyCode, $data);
    }

    public function departmentDecision(string $propertyCode, int $id, bool $approve, ?string $remarks = null, ?int $userId = null)
    {
        $leave = $this->repo->findByIdAndProperty($id, $propertyCode);
        if (!$leave) return null;

        $leave->dept_approved_by = $userId;
        $leave->dept_approved_at = $approve ? now() : null;
        $leave->dept_approval_remarks = $remarks;
        $leave->status = $approve ? 'approved_by_dept' : 'rejected_by_dept';
        $leave->is_approved = false;
        $leave->save();

        // audit
        $this->approvalRepo->create([
            'property_code' => $propertyCode,
            'leave_id' => $leave->id,
            'action' => $approve ? 'dept_approve' : 'dept_reject',
            'performed_by' => $userId,
            'performed_at' => now(),
            'remarks' => $remarks,
        ]);

        return $leave;
    }

    public function hrDecision(string $propertyCode, int $id, bool $approve, ?string $remarks = null, ?int $userId = null)
    {
        $leave = $this->repo->findByIdAndProperty($id, $propertyCode);
        if (!$leave) return null;

        $leave->hr_approved_by = $userId;
        $leave->hr_approved_at = $approve ? now() : null;
        $leave->hr_approval_remarks = $remarks;
        $leave->status = $approve ? 'approved_by_hr' : 'rejected_by_hr';
        $leave->is_approved = $approve;
        $leave->processed_at = $approve ? now() : null;
        $leave->save();

        // audit
        $this->approvalRepo->create([
            'property_code' => $propertyCode,
            'leave_id' => $leave->id,
            'action' => $approve ? 'hr_approve' : 'hr_reject',
            'performed_by' => $userId,
            'performed_at' => now(),
            'remarks' => $remarks,
        ]);

        return $leave;
    }

    public function delete(string $propertyCode, int $id): bool
    {
        // create audit for cancellation if exists
        $leave = $this->repo->findByIdAndProperty($id, $propertyCode);
        if ($leave) {
            $this->approvalRepo->create([
                'property_code' => $propertyCode,
                'leave_id' => $id,
                'action' => 'cancelled',
                'performed_by' => null,
                'performed_at' => now(),
                'remarks' => 'Deleted/cancelled by user',
            ]);
        }
        return $this->repo->delete($id, $propertyCode);
    }
}
