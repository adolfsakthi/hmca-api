<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreLeaveRequest;
use App\Http\Requests\HR\UpdateLeaveRequest;
use App\Services\HR\LeaveService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="HRMS - Leaves", description="Apply and manage employee leaves")
 */
class LeaveController extends Controller
{
    protected LeaveService $service;

    public function __construct(LeaveService $service)
    {
        $this->service = $service;
    }

    /**
     * Apply leave
     * @OA\Post(path="/api/hrms/leaves", tags={"HRMS - Leaves"}, summary="Apply for a leave")
     */
    public function store(StoreLeaveRequest $request)
    {
        $propertyCode = $request->get('property_code');
        $data = $request->validated();
        $leave = $this->service->apply($propertyCode, $data);
        return response()->json(['success' => true, 'data' => $leave], 201);
    }

    /**
     * List leaves (all)
     * @OA\Get(path="/api/hrms/leaves", tags={"HRMS - Leaves"}, summary="List all leaves (all statuses)")
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        $data = $this->service->list($propertyCode);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Show leave detail
     * @OA\Get(path="/api/hrms/leaves/{id}", tags={"HRMS - Leaves"}, summary="Get leave detail")
     */
    public function show(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $leave = $this->service->get($propertyCode, $id);
        if (!$leave) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'data' => $leave]);
    }

    /**
     * Department head decision
     * @OA\Post(path="/api/hrms/leaves/{id}/department-decision", tags={"HRMS - Leaves"}, summary="Department decision (approve/reject)")
     */
    public function departmentDecision(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $payload = $request->validate([
            'approve' => 'required|boolean',
            'remarks' => 'nullable|string'
        ]);
        $userId = $request->user()->id ?? null;
        $res = $this->service->departmentDecision($propertyCode, $id, $payload['approve'], $payload['remarks'] ?? null, $userId);
        if (!$res) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'data' => $res]);
    }

    /**
     * HR/Admin final decision
     * @OA\Post(path="/api/hrms/leaves/{id}/hr-decision", tags={"HRMS - Leaves"}, summary="HR/Admin decision (approve/reject)")
     */
    public function hrDecision(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $payload = $request->validate([
            'approve' => 'required|boolean',
            'remarks' => 'nullable|string'
        ]);
        $userId = $request->user()->id ?? null;
        $res = $this->service->hrDecision($propertyCode, $id, $payload['approve'], $payload['remarks'] ?? null, $userId);
        if (!$res) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'data' => $res]);
    }

    /**
     * Cancel / delete leave
     * @OA\Delete(path="/api/hrms/leaves/{id}", tags={"HRMS - Leaves"}, summary="Cancel/Delete leave")
     */
    public function destroy(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $ok = $this->service->delete($propertyCode, $id);
        if (!$ok) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'message' => 'Deleted']);
    }
}
