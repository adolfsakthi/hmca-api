<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PMS\RoomTypeService;

/**
 * @OA\Tag(
 *     name="RoomType",
 *     description="Manage Room Types for each property"
 * )
 *
 * @OA\Schema(
 *     schema="RoomType",
 *     title="RoomType",
 *     description="Room Type model",
 *     required={"name","property_code"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Deluxe Room"),
 *     @OA\Property(property="active", type="boolean", example=true)
 * )
 */
class RoomTypeController extends Controller
{
    protected $roomTypeService;

    public function __construct(RoomTypeService $roomTypeService)
    {
        $this->roomTypeService = $roomTypeService;
    }

    /**
     * @OA\Get(
     *     path="/api/pms/room-type",
     *     tags={"RoomType"},
     *     summary="Get all room types",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of room types",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/RoomType"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = request()->get('property_code');
        return $this->roomTypeService->getAllRoomTypes($propertyCode);
    }

    /**
     * @OA\Post(
     *     path="/api/pms/room-type",
     *     tags={"RoomType"},
     *     summary="Create a new room type",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","property_code"},
     *             @OA\Property(property="name", type="string", example="Deluxe Room"),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Room type created successfully"),
     *     @OA\Response(response=409, description="Room type already exists")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'nullable|boolean',
            'property_code' => 'required|string'
        ]);

        return $this->roomTypeService->createRoomType($validated);
    }

    /**
     * @OA\Get(
     *     path="/api/pms/room-type/{id}",
     *     tags={"RoomType"},
     *     summary="Get room type by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Room type ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Room type found"),
     *     @OA\Response(response=404, description="Room type not found")
     * )
     */
    public function show(int $id, Request $request)
    {
        $propertyCode = request()->get('property_code');
        return $this->roomTypeService->getRoomTypeById($id, $propertyCode);
    }

    /**
     * @OA\Put(
     *     path="/api/pms/room-type/{id}",
     *     tags={"RoomType"},
     *     summary="Update room type by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Room type ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Premium Suite"),
     *             @OA\Property(property="active", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Room type updated successfully"),
     *     @OA\Response(response=404, description="Room type not found")
     * )
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'nullable|boolean',
            'property_code' => 'required|string'
        ]);

        return $this->roomTypeService->updateRoomType($id, $validated);
    }

    /**
     * @OA\Delete(
     *     path="/api/pms/room-type/{id}",
     *     tags={"RoomType"},
     *     summary="Delete room type by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Room type ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Room type deleted successfully"),
     *     @OA\Response(response=404, description="Room type not found")
     * )
     */
    public function destroy(int $id,Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->roomTypeService->deleteRoomType($id,$propertyCode);
    }
}
