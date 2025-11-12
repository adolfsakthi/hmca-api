<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreLeaveTypeRequest;
use App\Http\Requests\HR\UpdateLeaveTypeRequest;
use App\Services\HR\LeaveTypeService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="HRMS - Leave Types", description="Manage leave types")
 *
 */
class LeaveTypeController extends Controller
{
    protected LeaveTypeService $service;

    public function __construct(LeaveTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/hrms/leave-types",
     *     tags={"HRMS - Leave Types"},
     *     summary="List all leave types",
     *     @OA\Response(response=200, description="List of leave types")
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
     *     path="/api/hrms/leave-types",
     *     tags={"HRMS - Leave Types"},
     *     summary="Create a new leave type",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","short_name","yearly_limit"},
     *             @OA\Property(property="name", type="string", example="Casual Leave"),
     *             @OA\Property(property="short_name", type="string", example="CL"),
     *             @OA\Property(property="yearly_limit", type="integer", example=12),
     *             @OA\Property(property="carry_forward_limit", type="integer", example=6),
     *             @OA\Property(property="consider_as", type="string", example="Paid"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Used for general leave")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Leave type created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreLeaveTypeRequest $request)
    {
        $propertyCode = $request->get('property_code');
        $lt = $this->service->create($propertyCode, $request->validated());
        return response()->json(['success' => true, 'data' => $lt], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/hrms/leave-types/{id}",
     *     tags={"HRMS - Leave Types"},
     *     summary="Update a leave type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Leave type ID",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Casual Leave"),
     *             @OA\Property(property="short_name", type="string", example="CL"),
     *             @OA\Property(property="yearly_limit", type="integer", example=12),
     *             @OA\Property(property="carry_forward_limit", type="integer", example=6),
     *             @OA\Property(property="consider_as", type="string", example="Paid"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Used for general leave")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Leave type updated successfully"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(UpdateLeaveTypeRequest $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $lt = $this->service->update($propertyCode, $id, $request->validated());
        return response()->json(['success' => true, 'data' => $lt]);
    }

    /**
     * @OA\Delete(
     *     path="/api/hrms/leave-types/{id}",
     *     tags={"HRMS - Leave Types"},
     *     summary="Delete a leave type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Leave type ID",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Response(response=200, description="Leave type deleted successfully"),
     *     @OA\Response(response=404, description="Leave type not found")
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
