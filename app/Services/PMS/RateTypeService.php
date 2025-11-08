<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\RateTypeRepositoryInterface;

class RateTypeService
{
    protected $rateTypeRepository;

    public function __construct(RateTypeRepositoryInterface $rateTypeRepository)
    {
        $this->rateTypeRepository = $rateTypeRepository;
    }

    public function getAllRateTypes(string $propertyCode)
    {
        $rateTypes = $this->rateTypeRepository->getAllByProperty($propertyCode);
        return response()->json([
            'success' => true,
            'message' => 'Rate types fetched successfully.',
            'data' => $rateTypes
        ]);
    }

    public function getRateTypesByRoom(string $propertyCode, int $roomId)
    {
        $rateTypes = $this->rateTypeRepository->getByRoomIdAndProperty($roomId, $propertyCode);
        return response()->json([
            'success' => true,
            'message' => 'Rate types fetched successfully for the room.',
            'data' => $rateTypes
        ]);
    }

    public function getRateTypeById(int $id, string $propertyCode)
    {
        $rateType = $this->rateTypeRepository->findByIdByProperty($id, $propertyCode);

        if (!$rateType) {
            return response()->json([
                'success' => false,
                'message' => 'Rate type not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rate type fetched successfully.',
            'data' => $rateType
        ]);
    }

    public function createRateType(array $data)
    {
        $rateType = $this->rateTypeRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Rate type created successfully.',
            'data' => $rateType
        ], 201);
    }

    public function updateRateType(int $id, array $data)
    {
        $rateType = $this->rateTypeRepository->findByIdByProperty($id, $data['property_code']);

        if (!$rateType) {
            return response()->json([
                'success' => false,
                'message' => 'Rate type not found.'
            ], 404);
        }

        $updatedRateType = $this->rateTypeRepository->update($rateType, $data);

        return response()->json([
            'success' => true,
            'message' => 'Rate type updated successfully.',
            'data' => $updatedRateType
        ]);
    }

    public function deleteRateType(int $id, string $propertyCode)
    {
        $rateType = $this->rateTypeRepository->findByIdByProperty($id, $propertyCode);

        if (!$rateType) {
            return response()->json([
                'success' => false,
                'message' => 'Rate type not found.'
            ], 404);
        }

        $deleted = $this->rateTypeRepository->delete($rateType);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted
                ? 'Rate type deleted successfully.'
                : 'Failed to delete rate type.'
        ], $deleted ? 200 : 500);
    }
}
