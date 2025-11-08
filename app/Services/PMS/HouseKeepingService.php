<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\HousekeepingRepositoryInterface;
use Illuminate\Support\Facades\Log;

class HousekeepingService
{
    protected $housekeepingRepository;

    public function __construct(HousekeepingRepositoryInterface $housekeepingRepository)
    {
        $this->housekeepingRepository = $housekeepingRepository;
    }

    public function getAll(string $propertyCode)
    {
        $data = $this->housekeepingRepository->getAllByProperty($propertyCode);

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping data fetched successfully.',
            'data' => $data
        ], 200);
    }

    public function getById(int $id, string $propertyCode)
    {
        $data = $this->housekeepingRepository->findByIdByProperty($id, $propertyCode);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping record fetched successfully.',
            'data' => $data
        ], 200);
    }

    public function create(array $data)
    {
        $created = $this->housekeepingRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping record created successfully.',
            'data' => $created
        ], 201);
    }

    public function update(int $id, array $data)
    {
        $housekeeping = $this->housekeepingRepository->findByIdByProperty($id, $data['property_code']);

        if (!$housekeeping) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }

        $updated = $this->housekeepingRepository->update($housekeeping, $data);

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping updated successfully.',
            'data' => $updated
        ], 200);
    }

    public function delete(int $id, string $propertyCode)
    {
        $housekeeping = $this->housekeepingRepository->findByIdByProperty($id, $propertyCode);

        if (!$housekeeping) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }

        $deleted = $this->housekeepingRepository->delete($housekeeping);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Deleted successfully.' : 'Failed to delete.'
        ], $deleted ? 200 : 500);
    }
}
