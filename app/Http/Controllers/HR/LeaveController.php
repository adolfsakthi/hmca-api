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
     * List leaves (all)
     * @OA\Get(
     *      path="/api/hrms/leaves",
     *      tags={"HRMS - Leaves"},
     *      summary="List all leaves (all statuses)",
     *      @OA\Response(response=200, description="List of all leaves"),
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        $data = $this->service->list($propertyCode);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * @OA\Post(
     *     path="/api/hrms/leaves",
     *     tags={"HRMS - Leaves"},
     *     summary="Apply for a leave",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"employee_id","leave_type_id","duration_unit","from_date","to_date"},
     *             @OA\Property(property="employee_id", type="integer", example=12),
     *             @OA\Property(property="leave_type_id", type="integer", example=3),
     *             @OA\Property(property="duration_unit", type="string", example="full"),
     *             @OA\Property(property="from_date", type="string", format="date", example="2025-11-12"),
     *             @OA\Property(property="to_date", type="string", format="date", example="2025-11-14"),
     *             @OA\Property(property="remarks", type="string", nullable=true, example="Personal work")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Leave applied successfully"),
     *     @OA\Response(response=401, description="Unauthorized - Invalid or missing token"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreLeaveRequest $request)
    {
        $propertyCode = $request->get('property_code');
        $data = $request->validated();
        $leave = $this->service->apply($propertyCode, $data);
        return response()->json(['success' => true, 'data' => $leave], 201);
    }
    /**
     * Show leave detail
     * @OA\Get(
     *      path="/api/hrms/leaves/{id}",
     *      tags={"HRMS - Leaves"}, 
     *      summary="Get leave detail",
     *      @OA\Response(response=200, description="Leave details")
     * ),
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
     * @OA\Post(
     *      path="/api/hrms/leaves/{id}/department-decision",
     *      tags={"HRMS - Leaves"}, 
     *      summary="Department decision (approve/reject)",
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Leave ID",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"approve"},
     *             @OA\Property(property="approve", type="boolean", example=true),
     *             @OA\Property(property="remarks", type="string", example="Approved - all clear")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Decision updated successfully"),
     *     @OA\Response(response=404, description="Leave not found"),
     *  )
     * 
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
     * @OA\Post(path="/api/hrms/leaves/{id}/hr-decision", tags={"HRMS - Leaves"}, summary="HR/Admin decision (approve/reject)",
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Leave ID",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"approve"},
     *             @OA\Property(property="approve", type="boolean", example=false),
     *             @OA\Property(property="remarks", type="string", example="Insufficient leave balance")
     *         )
     *     ),
     *     @OA\Response(response=200, description="HR decision applied successfully"),
     *     @OA\Response(response=404, description="Leave not found")
     * )
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
     * @OA\Delete(path="/api/hrms/leaves/{id}",
     * tags={"HRMS - Leaves"}, summary="Cancel/Delete leave",
     * @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Leave ID",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *     @OA\Response(response=200, description="Leave deleted successfully"),
     *     @OA\Response(response=404, description="Leave not found")
     * )
     */
    public function destroy(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $ok = $this->service->delete($propertyCode, $id);
        if (!$ok) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'message' => 'Deleted']);
    }
}
