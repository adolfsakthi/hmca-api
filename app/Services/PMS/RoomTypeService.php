<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\RoomTypeRepositoryInterface;
use App\Models\PMS\RoomType;

class RoomTypeService
{
    protected $roomTypeRepository;

    public function __construct(RoomTypeRepositoryInterface $roomTypeRepository)
    {
        $this->roomTypeRepository = $roomTypeRepository;
    }

    public function getAllRoomTypes(string $propertyCode)
    {
        $roomTypes = $this->roomTypeRepository->getAllByProperty($propertyCode);

        return response()->json([
            'success' => true,
            'message' => 'Room types fetched successfully.',
            'data' => $roomTypes
        ], 200);
    }

    public function getRoomTypeById(int $id,string $propertyCode)
    {
        $roomType = $this->roomTypeRepository->findByIdByProperty($id,$propertyCode);

        if (!$roomType) {
            return response()->json([
                'success' => false,
                'message' => 'Room type not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Room type fetched successfully.',
            'data' => $roomType
        ], 200);
    }

    public function createRoomType(array $data)
    {

        $exists = $this->roomTypeRepository->findByPropertyCode($data['name'], $data['property_code']);

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Room type with this name already exists for the property.'
            ], 409);
        }

        $roomType = $this->roomTypeRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Room type created successfully.',
            'data' => $roomType
        ], 201);
    }

    public function updateRoomType(int $id, array $data)
    {
        $roomType = $this->roomTypeRepository->findByIdByProperty($id, $data['property_code']);

        if (!$roomType) {
            return response()->json([
                'success' => false,
                'message' => 'Room type not found.'
            ], 404);
        }


        $exists = $this->roomTypeRepository->findByPropertyCode(
            $data['name'],
            $data['property_code']
        );

        if ($exists && $exists->id !== $id) {
            return response()->json([
                'success' => false,
                'message' => 'Room type with this name already exists for the property.'
            ], 409);
        }

        $this->roomTypeRepository->update($roomType, $data);

        return response()->json([
            'success' => true,
            'message' => 'Room type updated successfully.',
            'data' => $roomType
        ], 200);
    }

    public function deleteRoomType(int $id,string $propertyCode)
    {
        $roomType = $this->roomTypeRepository->findByIdByProperty($id, $propertyCode);

        if (!$roomType) {
            return response()->json([
                'success' => false,
                'message' => 'Room type not found.'
            ], 404);
        }

        $this->roomTypeRepository->delete($roomType);

        return response()->json([
            'success' => true,
            'message' => 'Room type deleted successfully.'
        ], 200);
    }
}
