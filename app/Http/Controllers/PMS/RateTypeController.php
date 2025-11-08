<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PMS\RateTypeService;

/**
 * @OA\Tag(
 *     name="Rate Types (PMS)",
 *     description="Manage room rate types such as Room Only, Bed & Breakfast, etc."
 * )
 */
class RateTypeController extends Controller
{
    protected $rateTypeService;

    public function __construct(RateTypeService $rateTypeService)
    {
        $this->rateTypeService = $rateTypeService;
    }

    /**
     * @OA\Get(
     *     path="/api/pms/rate-types",
     *     tags={"Rate Types (PMS)"},
     *     summary="Get all rate types for a property",
     *     description="Retrieve all rate types associated with a given property code.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="List of rate types fetched successfully"),
     *     @OA\Response(response=404, description="Property not found")
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->rateTypeService->getAllRateTypes($propertyCode);
    }

    /**
     * @OA\Get(
     *     path="/api/pms/rate-types/{id}",
     *     tags={"Rate Types (PMS)"},
     *     summary="Get a specific rate type by ID",
     *     description="Fetch details of a specific rate type by ID within a property.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Rate type ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Rate type details fetched successfully"),
     *     @OA\Response(response=404, description="Rate type not found")
     * )
     */
    public function show(Request $request, $id)
    {
        $propertyCode = $request->get('property_code');
        return $this->rateTypeService->getRateTypeById($id, $propertyCode);
    }

    /**
     * @OA\Get(
     *     path="/api/pms/rate-types/room/{roomId}",
     *     tags={"Rate Types (PMS)"},
     *     summary="Get rate types by room",
     *     description="Fetch all rate types available for a specific room based on its room type and property.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="roomId",
     *         in="path",
     *         required=true,
     *         description="Room ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Rate types fetched successfully for the room"),
     *     @OA\Response(response=404, description="Room not found")
     * )
     */
    public function getByRoom(Request $request, $roomId)
    {
        $propertyCode = $request->get('property_code');
        return $this->rateTypeService->getRateTypesByRoom($propertyCode, $roomId);
    }

    /**
     * @OA\Post(
     *     path="/api/pms/rate-types",
     *     tags={"Rate Types (PMS)"},
     *     summary="Create a new rate type",
     *     description="Admin can create a new rate type for a specific property and room type.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_code", "room_type_id", "name", "base_price"},
     *             @OA\Property(property="room_type_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Bed & Breakfast"),
     *             @OA\Property(property="description", type="string", example="Includes breakfast for 2 adults."),
     *             @OA\Property(property="base_price", type="number", format="float", example=4000),
     *         )
     *     ),
     *     @OA\Response(response=201, description="Rate type created successfully"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'room_type_id' => 'required|exists:room_types,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
        ]);

        return $this->rateTypeService->createRateType($validated);
    }

    /**
     * @OA\Put(
     *     path="/api/pms/rate-types/{id}",
     *     tags={"Rate Types (PMS)"},
     *     summary="Update an existing rate type",
     *     description="Edit an existing rate type for a specific property.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Rate type ID to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_code"},
     *             @OA\Property(property="name", type="string", example="Room Only"),
     *             @OA\Property(property="description", type="string", example="Stay without meals"),
     *             @OA\Property(property="base_price", type="number", format="float", example=3500)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Rate type updated successfully"),
     *     @OA\Response(response=404, description="Rate type not found")
     * )
     */
    public function update(Request $request, $propertyCode, $id)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'room_type_id' => 'sometimes|exists:room_types,id',
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'base_price' => 'sometimes|numeric|min:0',
        ]);

        return $this->rateTypeService->updateRateType($id, $validated);
    }

    /**
     * @OA\Delete(
     *     path="/api/pms/rate-types/{id}",
     *     tags={"Rate Types (PMS)"},
     *     summary="Delete a rate type",
     *     description="Remove a rate type from a property’s rate list.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Rate type ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Rate type deleted successfully"),
     *     @OA\Response(response=404, description="Rate type not found")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $propertyCode = $request->get('property_code');
        return $this->rateTypeService->deleteRateType($id, $propertyCode);
    }
}
