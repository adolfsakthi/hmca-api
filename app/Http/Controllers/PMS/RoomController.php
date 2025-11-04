<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PMS\RoomService;

/**
 * @OA\Tag(
 *     name="Rooms",
 *     description="Room management APIs"
 * )
 */
class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    /**
     * @OA\Get(
     *     path="/api/rooms",
     *     tags={"Rooms"},
     *     summary="Get all rooms for the property",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="List of rooms retrieved successfully")
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->roomService->getAllRooms($propertyCode);
    }

    /**
     * @OA\Post(
     *     path="/api/rooms",
     *     tags={"Rooms"},
     *     summary="Create a new room",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"room_type_id","room_number","capacity"},
     *             @OA\Property(property="room_type_id", type="integer", example=1),
     *             @OA\Property(property="room_number", type="string", example="A101"),
     *             @OA\Property(property="capacity", type="integer", example=2),
     *             @OA\Property(property="extra_capability", type="integer", example=1),
     *             @OA\Property(property="room_price", type="number", format="float", example=2500.50),
     *             @OA\Property(property="bed_charge", type="number", format="float", example=300.00),
     *             @OA\Property(property="room_size", type="string", example="single"),
     *             @OA\Property(property="bed_number", type="integer", example=2),
     *             @OA\Property(property="bed_type", type="string", example="kingbed"),
     *             @OA\Property(property="room_description", type="string", example="Deluxe AC Room"),
     *             @OA\Property(property="reserve_condition", type="string", example="Non-refundable"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(
     *                 property="amenity_ids",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1),
     *                 description="Array of amenity IDs"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Room created successfully"),
     *     @OA\Response(response=400, description="Invalid data provided")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'extra_capability' => 'nullable|integer',
            'room_price' => 'nullable|numeric|min:0',
            'bed_charge' => 'nullable|numeric|min:0',
            'room_size' => 'nullable|string',
            'bed_number' => 'nullable|integer|min:0',
            'bed_type' => 'nullable|string',
            'room_description' => 'nullable|string',
            'reserve_condition' => 'nullable|string',
            'is_active' => 'boolean',
            'amenity_ids' => 'array',
            'amenity_ids.*' => 'exists:amenities,id'
        ]);

        $validated['property_code'] = $request->get('property_code');

        return $this->roomService->CreateRoom($validated);
    }

    /**
     * @OA\Get(
     *     path="/api/rooms/{id}",
     *     tags={"Rooms"},
     *     summary="Get a room by ID",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Room details fetched successfully"),
     *     @OA\Response(response=404, description="Room not found")
     * )
     */
    public function show(int $id, Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->roomService->getRoomById($id, $propertyCode);
    }

    /**
     * @OA\Put(
     *     path="/api/rooms/{id}",
     *     tags={"Rooms"},
     *     summary="Update an existing room",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="room_type_id", type="integer", example=2),
     *             @OA\Property(property="room_number", type="string", example="A202"),
     *             @OA\Property(property="capacity", type="integer", example=4),
     *             @OA\Property(property="room_price", type="number", format="float", example=3500.00),
     *             @OA\Property(property="bed_type", type="string", example="kingbed"),
     *             @OA\Property(property="is_active", type="boolean", example=false),
     *             @OA\Property(property="extra_capability", type="integer", example=2),
     *             @OA\Property(property="bed_charge", type="number", format="float", example=400.00),
     *            @OA\Property(property="room_size", type="string", example="single"),
     *            @OA\Property(property="bed_number", type="integer", example=1),
     *            @OA\Property(property="room_description", type="string", example="Updated room description"),
     *            @OA\Property(property="reserve_condition", type="string", example="Refundable"),
     *             @OA\Property(
     *                 property="amenity_ids",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Room updated successfully"),
     *     @OA\Response(response=404, description="Room not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'room_type_id' => 'nullable|exists:room_types,id',
            'room_number' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'extra_capability' => 'nullable|integer',
            'room_price' => 'nullable|numeric|min:0',
            'bed_charge' => 'nullable|numeric|min:0',
            'room_size' => 'nullable|string',
            'bed_number' => 'nullable|integer|min:0',
            'bed_type' => 'nullable|string',
            'room_description' => 'nullable|string',
            'reserve_condition' => 'nullable|string',
            'is_active' => 'boolean',
            'amenity_ids' => 'array',
            'amenity_ids.*' => 'exists:amenities,id'
        ]);

        return $this->roomService->updateRoom($id, $validated);
    }

    /**
     * @OA\Delete(
     *     path="/api/rooms/{id}",
     *     tags={"Rooms"},
     *     summary="Delete a room",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Room deleted successfully"),
     *     @OA\Response(response=404, description="Room not found")
     * )
     */
    public function destroy($id, Request $request)
    {
        $propertyCode = $request->get('property_code');

        return $this->roomService->deleteRoom($id, $propertyCode);
    }
}
