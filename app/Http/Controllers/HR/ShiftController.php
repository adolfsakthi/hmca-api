<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreShiftRequest;
use App\Http\Requests\HR\UpdateShiftRequest;
use App\Services\HR\ShiftService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="HRMS Shifts", description="Manage shifts")
 */
class ShiftController extends Controller
{
    protected ShiftService $service;

    public function __construct(ShiftService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/shifts",
     *   tags={"HRMS Shifts"},
     *   summary="List shifts for property",
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="q", in="query", description="search by code or name"),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        $perPage = $request->get('per_page') ? (int)$request->get('per_page') : null;
        $search = $request->get('q');

        $data = $this->service->list($propertyCode, $perPage, $search);
        return response()->json($data, 200);
    }

    /**
     * @OA\Post(
     *   path="/api/hrms/shifts",
     *   tags={"HRMS Shifts"},
     *   summary="Create a shift",
     *   @OA\RequestBody(@OA\JsonContent(
     *     required={"code","name"},
     *     @OA\Property(property="code", type="string"),
     *     @OA\Property(property="name", type="string"),
     *     @OA\Property(property="start_time", type="string", example="09:00"),
     *     @OA\Property(property="end_time", type="string", example="17:00")
     *   )),
     *   @OA\Response(response=201, description="Created"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreShiftRequest $request)
    {
        $propertyCode = $request->get('property_code');
        $payload = $request->validated();

        $shift = $this->service->create($propertyCode, $payload);
        return response()->json(['success' => true, 'data' => $shift], 201);
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/shifts/{id}",
     *   tags={"HRMS Shifts"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $shift = $this->service->get($propertyCode, $id);
        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Shift not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $shift], 200);
    }

    /**
     * @OA\Put(
     *   path="/api/hrms/shifts/{id}",
     *   tags={"HRMS Shifts"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(@OA\JsonContent()),
     *   @OA\Response(response=200, description="Updated"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(UpdateShiftRequest $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $payload = $request->validated();

        try {
            $shift = $this->service->update($propertyCode, $id, $payload);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Shift not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $shift], 200);
    }

    /**
     * @OA\Delete(
     *   path="/api/hrms/shifts/{id}",
     *   tags={"HRMS Shifts"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Deleted"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $ok = $this->service->delete($propertyCode, $id);
        if (!$ok) {
            return response()->json(['success' => false, 'message' => 'Shift not found'], 404);
        }
        return response()->json(['success' => true, 'message' => 'Deleted'], 200);
    }
}
