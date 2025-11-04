<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SuperAdmin\PropertyService;

/**
 * @OA\Tag(
 *     name="Property",
 *     description="Manage hotel/property data"
 * )
 *
 * @OA\Schema(
 *     schema="Property",
 *     title="Property",
 *     description="Property model",
 *     required={"property_name", "property_code", "email"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="property_name", type="string", example="My Hotel"),
 *     @OA\Property(property="property_code", type="string", example="PROP001"),
 *     @OA\Property(property="email", type="string", format="email", example="info@example.com"),
 *     @OA\Property(property="phone", type="string", example="+1-555-555-5555"),
 *     @OA\Property(property="address", type="string", example="123 Main St"),
 *     @OA\Property(property="city", type="string", example="Chennai"),
 *     @OA\Property(property="state", type="string", example="TN"),
 *     @OA\Property(property="zip_code", type="string", example="600001"),
 *     @OA\Property(property="country", type="string", example="India"),
 *     @OA\Property(property="description", type="string", example="A short description"),
 *     @OA\Property(property="billing_active", type="boolean", example=true)
 * )
 */
class PropertyController extends Controller
{
    protected PropertyService $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    /**
     * @OA\Get(
     *     path="/api/property",
     *     tags={"Property"},
     *     summary="Get all properties",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all properties",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Property"))
     *     )
     * )
     */
    public function index()
    {
        // Service returns a collection — avoid re-encoding it again
        return $this->propertyService->getAllProperties();
    }

    /**
     * @OA\Post(
     *     path="/api/property",
     *     tags={"Property"},
     *     summary="Create a new property",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_name","property_code","email","address"},
     *             @OA\Property(property="property_name", type="string", example="My Hotel"),
     *             @OA\Property(property="property_code", type="string", example="PROP001"),
     *             @OA\Property(property="email", type="string", format="email", example="admin@myhotel.com"),
     *             @OA\Property(property="phone", type="string", example="+1-555-555-5555"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="Chennai"),
     *             @OA\Property(property="state", type="string", example="TN"),
     *             @OA\Property(property="zip_code", type="string", example="600001"),
     *             @OA\Property(property="country", type="string", example="India"),
     *             @OA\Property(property="description", type="string", example="A luxury hotel in downtown")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Property created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Property")
     *     ),
     *     @OA\Response(response=409, description="Duplicate property code or email")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_name' => 'required|string|max:255',
            'property_code' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        return $this->propertyService->createProperty($validated);
    }

    /**
     * @OA\Get(
     *     path="/api/property/{id}",
     *     tags={"Property"},
     *     summary="Get a property by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Property ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Property found", @OA\JsonContent(ref="#/components/schemas/Property")),
     *     @OA\Response(response=404, description="Property not found")
     * )
     */
    public function show(int $id)
    {
        return $this->propertyService->getPropertyById($id);
    }

    /**
     * @OA\Put(
     *     path="/api/property/{id}",
     *     tags={"Property"},
     *     summary="Update an existing property",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Property ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="string", example="+1-555-555-5555"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="Chennai"),
     *             @OA\Property(property="state", type="string", example="TN"),
     *             @OA\Property(property="zip_code", type="string", example="600001"),
     *             @OA\Property(property="country", type="string", example="India"),
     *             @OA\Property(property="description", type="string", example="Updated description")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Property updated successfully"),
     *     @OA\Response(response=404, description="Property not found")
     * )
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'phone' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        return $this->propertyService->updateProperty($id, $validated);
    }

    /**
     * @OA\Delete(
     *     path="/api/property/{id}",
     *     tags={"Property"},
     *     summary="Delete a property",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Property ID to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Property deleted successfully"),
     *     @OA\Response(response=404, description="Property not found")
     * )
     */
    public function destroy(int $id)
    {
        return $this->propertyService->deleteProperty($id);
    }

    /**
     * @OA\Put(
     *     path="/api/property/temporaryDisable/{id}",
     *     tags={"Property"},
     *     summary="Temporarily disable property billing",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Property ID to disable",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Property temporarily disabled successfully"),
     *     @OA\Response(response=404, description="Property not found")
     * )
     */
    public function temporaryDisable(int $id)
    {
        return $this->propertyService->temporaryDisableProperty($id);
    }
}
