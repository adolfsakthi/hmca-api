<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PMS\AmenityService;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Amenity",
 *     description="Manage property amenities"
 * )
 */
class AmenityController extends Controller
{
    protected AmenityService $amenityService;

    public function __construct(AmenityService $amenityService)
    {
        $this->amenityService = $amenityService;
    }
    /**
     * @OA\Get(
     *     path="/api/pms/amenities",
     *     tags={"Amenity"},
     *     summary="Get all amenities",
     *     @OA\Response(response=200, description="List of amenities")
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->amenityService->getAllAmenities($propertyCode);
    }


    /**
     * @OA\Get(
     *     path="/api/pms/amenities/{id}",
     *     tags={"Amenity"},
     *     summary="Get amenity by ID",
     *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),   
     *     @OA\Response(response=200, description="Amenity details fetched successfully")
     * )
     */
    public function show(int $id, Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->amenityService->getAmenityById($id, $propertyCode);
    }

    /**
     * @OA\Post(
     *     path="/api/pms/amenities",
     *     tags={"Amenity"},
     *     summary="Create a new amenity",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_code", "name"},
     *             @OA\Property(property="name", type="string", example="Wi-Fi"),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Amenity created successfully")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
            'property_code' => 'required|string'
        ]);

        return $this->amenityService->createAmenity($validated);
    }

    /**
     * @OA\Put(
     *     path="/api/pms/amenities/{id}",
     *     tags={"Amenity"},
     *     summary="Update an existing amenity",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Wi-Fi"),
     *             @OA\Property(property="active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Amenity updated successfully")
     * )
     */
    public function update(Request $request, int $id)
    {

        Log::info('Update request received for amenity ID: ' . $id);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
            'property_code' => 'required|string'
        ]);

        return $this->amenityService->updateAmenity($id, $validated);
    }

    /**
     * @OA\Delete(
     *     path="/api/pms/amenities/{id}",
     *     tags={"Amenity"},
     *     summary="Delete an amenity",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Amenity deleted successfully")
     * )
     */
    public function destroy(int $id, Request $request)
    {
        $propertyCode = $request->get('property_code');
        return $this->amenityService->deleteAmenity($id, $propertyCode);
    }
}
