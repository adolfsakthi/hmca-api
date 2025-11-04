<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\AmenityRepositoryInterface;
use Illuminate\Support\Facades\Log;

class AmenityService
{
    protected $amenityRepository;

    public function __construct(AmenityRepositoryInterface $amenityRepository)
    {
        $this->amenityRepository = $amenityRepository;
    }

    public function getAllAmenities(string $propertyCode)
    {
        $amenities = $this->amenityRepository->getAllByProperty($propertyCode);

        return response()->json([
            'success' => true,
            'message' => 'Amenities fetched successfully.',
            'data' => $amenities
        ], 200);
    }

    public function getAmenityById($id,string $propertyCode)
    {
        $amenity = $this->amenityRepository->findByIdByProperty($id,$propertyCode);

        if (!$amenity) {
            return response()->json([
                'success' => false,
                'message' => 'Amenity not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Amenity fetched successfully.',
            'data' => $amenity
        ], 200);
    }

    public function createAmenity(array $data)
    {
        $exists = $this->amenityRepository->findByPropertyCode($data['name'], $data['property_code']);

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Amenity with this name already exists for the property.'
            ], 409);
        }

        $amenity = $this->amenityRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Amenity created successfully.',
            'data' => $amenity
        ], 201);
    }

    public function updateAmenity(int $id, array $data)
    {
        Log::info('Updating amenity with data: ', $data);
        $amenity = $this->amenityRepository->findByIdByProperty($id, $data['property_code']);

        if (!$amenity) {
            return response()->json([
                'success' => false,
                'message' => 'Amenity not found.'
            ], 404);
        }

        Log::info('Found amenity: ', ['amenity' => $amenity]);



        $exists = $this->amenityRepository->findByPropertyCode(
            $data['name'],
            $data['property_code']
        );

        if ($exists && $exists->id !== $id) {
            return response()->json([
                'success' => false,
                'message' => 'Another amenity with this name already exists for this property.'
            ], 409);
        }

        $updatedAmenity = $this->amenityRepository->update($amenity, $data);

        return response()->json([
            'success' => true,
            'message' => 'Amenity updated successfully.',
            'data' => $updatedAmenity
        ], 200);
    }

    public function deleteAmenity(int $id,string $propertyCode)
    {
        $amenity = $this->amenityRepository->findByIdByProperty($id, $propertyCode);

        if (!$amenity) {
            return response()->json([
                'success' => false,
                'message' => 'Amenity not found.'
            ], 404);
        }
        
        $deleted = $this->amenityRepository->delete($amenity);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted 
                ? 'Amenity deleted successfully.'
                : 'Failed to delete amenity.'
        ], $deleted ? 200 : 500);
    }
}
