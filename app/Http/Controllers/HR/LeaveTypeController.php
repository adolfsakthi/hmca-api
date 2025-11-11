<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreLeaveTypeRequest;
use App\Http\Requests\HR\UpdateLeaveTypeRequest;
use App\Services\HR\LeaveTypeService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="HRMS - Leave Types", description="Manage leave types")
 */
class LeaveTypeController extends Controller
{
    protected LeaveTypeService $service;

    public function __construct(LeaveTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(path="/api/hrms/leave-types", tags={"HRMS - Leave Types"}, summary="List leave types (no pagination)")
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        $data = $this->service->list($propertyCode);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * @OA\Post(path="/api/hrms/leave-types", tags={"HRMS - Leave Types"}, summary="Create leave type")
     */
    public function store(StoreLeaveTypeRequest $request)
    {
        $propertyCode = $request->get('property_code');
        $lt = $this->service->create($propertyCode, $request->validated());
        return response()->json(['success' => true, 'data' => $lt], 201);
    }

    /**
     * @OA\Put(path="/api/hrms/leave-types/{id}", tags={"HRMS - Leave Types"}, summary="Update leave type")
     */
    public function update(UpdateLeaveTypeRequest $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $lt = $this->service->update($propertyCode, $id, $request->validated());
        return response()->json(['success' => true, 'data' => $lt]);
    }

    /**
     * @OA\Delete(path="/api/hrms/leave-types/{id}", tags={"HRMS - Leave Types"}, summary="Delete leave type")
     */
    public function destroy(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $ok = $this->service->delete($propertyCode, $id);
        if (!$ok) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'message' => 'Deleted']);
    }
}
