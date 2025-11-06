<?php

namespace App\Services\PMS;

use App\Http\Resources\RoomResource;
use App\Repositories\PMS\Interfaces\RoomRepositoryInterface;

class RoomService
{
    protected $roomRepository;

    public function __construct(RoomRepositoryInterface $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    public function getAllRooms(string $propertyCode)
    {
        $rooms = $this->roomRepository->getAllByProperty($propertyCode);
        return response()->json([
            'success' => true,
            'message' => 'Rooms fetched successfully.',
            'data' => RoomResource::collection($rooms),
        ]);
    }

    public function getRoomById(int $id, string $propertyCode)
    {
        $room = $this->roomRepository->findByIdByProperty($id, $propertyCode);
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Room fetched successfully.',
            'data' => new RoomResource($room),
        ]);
    }

    public function createRoom(array $data)
    {

        $exists = $this->roomRepository->findByPropertyCode($data['room_number'], $data['property_code']);
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Room with this name already exists for the property.',
            ], 409);
        }

        $room = $this->roomRepository->create($data);

        if (isset($data['amenity_ids'])) {
            $room->amenities()->sync($data['amenity_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Room created successfully.',
            'data' => $room,
        ], 201);
    }

    public function updateRoom(int $id, array $data)
    {
        $room = $this->roomRepository->findByIdByProperty($id, $data['property_code']);
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found.',
            ], 404);
        }

        $duplicate = $this->roomRepository->findByPropertyCode(
            $data['room_number'],
            $data['property_code']
        );

        if ($duplicate && $duplicate->id !== $id) {
            return response()->json([
                'success' => false,
                'message' => 'Another room with this name already exists for this property.',
            ], 409);
        }

        $updatedRoom = $this->roomRepository->update($room, $data);

        if (isset($data['amenity_ids'])) {
            $updatedRoom->amenities()->sync($data['amenity_ids']);
        }

        $updatedRoom->load(['roomType', 'amenities']);

        return response()->json([
            'success' => true,
            'message' => 'Room updated successfully.',
            'data' => $updatedRoom,
        ]);
    }

    public function deleteRoom(int $id, string $propertyCode)
    {
        $room = $this->roomRepository->findByIdByProperty($id, $propertyCode);
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found.',
            ], 404);
        }

        $this->roomRepository->delete($room);

        return response()->json([
            'success' => true,
            'message' => 'Room deleted successfully.',
        ]);
    }
}
