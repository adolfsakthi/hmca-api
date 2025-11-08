<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PMS\HousekeepingService;

/**
 * @OA\Tag(
 *     name="Housekeeping",
 *     description="Manage housekeeping operations like cleaning, inspection, and maintenance."
 * )
 */
class HousekeepingController extends Controller
{
    protected $housekeepingService;

    public function __construct(HousekeepingService $housekeepingService)
    {
        $this->housekeepingService = $housekeepingService;
    }

    /**
     * @OA\Get(
     *     path="/api/pms/housekeeping",
     *     tags={"Housekeeping"},
     *     summary="Get all housekeeping records for a property",
     *     description="Fetches a list of all housekeeping records linked to a specific property code.",
     *     @OA\Response(response=200, description="Housekeeping data fetched successfully."),
     *     @OA\Response(response=422, description="Validation failed.")
     * )
     */
    public function index(Request $request)
    {
        $request->validate(['property_code' => 'required|string']);
        return $this->housekeepingService->getAll($request->property_code);
    }

    /**
     * @OA\Get(
     *     path="/api/pms/housekeeping/{id}",
     *     tags={"Housekeeping"},
     *     summary="Get a specific housekeeping record",
     *     description="Fetch a single housekeeping entry by ID and property code.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Housekeeping record ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Housekeeping record fetched successfully."),
     *     @OA\Response(response=404, description="Record not found.")
     * )
     */
    public function show(Request $request, int $id)
    {
        $request->validate(['property_code' => 'required|string']);
        return $this->housekeepingService->getById($id, $request->property_code);
    }

    /**
     * @OA\Post(
     *     path="/api/pms/housekeeping",
     *     tags={"Housekeeping"},
     *     summary="Create a new housekeeping record",
     *     description="Used by admin or housekeeping staff to create a housekeeping entry for a specific room.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"room_id","status"},
     *             @OA\Property(property="room_id", type="integer", example=101),
     *             @OA\Property(property="status", type="string", enum={"clean","dirty","inspected","maintenance"}, example="dirty"),
     *             @OA\Property(property="assigned_to_user_id", type="integer", example=1),
     *             @OA\Property(property="remarks", type="string", example="Guest checked out, needs cleaning")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Housekeeping record created successfully."),
     *     @OA\Response(response=422, description="Validation error.")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'room_id' => 'required|exists:rooms,id',
            'status' => 'required|string|in:clean,dirty,inspected,maintenance',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string|max:255',
        ]);

        return $this->housekeepingService->create($validated);
    }

    /**
     * @OA\Post(
     *     path="/api/pms/housekeeping/{id}",
     *     tags={"Housekeeping"},
     *     summary="Update housekeeping record",
     *     description="Update housekeeping details such as status, remarks, or assigned staff.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Housekeeping record ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"clean","dirty","inspected","maintenance"}, example="clean"),
     *             @OA\Property(property="assigned_to_user_id", type="integer", example=1),
     *             @OA\Property(property="remarks", type="string", example="Room cleaned and inspected")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Housekeeping record updated successfully."),
     *     @OA\Response(response=404, description="Record not found.")
     * )
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'status' => 'nullable|string|in:clean,dirty,inspected,maintenance',
            'remarks' => 'nullable|string|max:255',
            'assigned_to_user_id' => 'nullable|exists:users,id',
        ]);

        return $this->housekeepingService->update($id, $validated);
    }

    /**
     * @OA\Delete(
     *     path="/api/pms/housekeeping/{id}",
     *     tags={"Housekeeping"},
     *     summary="Delete housekeeping record",
     *     description="Removes a housekeeping entry from the database.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Housekeeping record ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Housekeeping record deleted successfully."),
     *     @OA\Response(response=404, description="Record not found.")
     * )
     */
    public function destroy(Request $request, int $id)
    {
        $request->validate(['property_code' => 'required|string']);
        return $this->housekeepingService->delete($id, $request->property_code);
    }
}
