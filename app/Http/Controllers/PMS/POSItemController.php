<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PMS\POSItemService;

class POSItemController extends Controller
{
    protected $posItemService;

    public function __construct(POSItemService $posItemService)
    {
        $this->posItemService = $posItemService;
    }

    /**
     * @OA\Get(
     *     path="/api/pms/pos/items",
     *     tags={"POS Items"},
     *     summary="Get all POS items for a property",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="List of POS items")
     * )
     */
    public function index(Request $request)
    {
        return $this->posItemService->getAll($request->get('property_code'));
    }

    /**
     * @OA\Post(
     *     path="/api/pms/pos/items",
     *     tags={"POS Items"},
     *     summary="Create new POS item",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_code","name","price"},
     *             @OA\Property(property="property_code", type="string", example="H001"),
     *             @OA\Property(property="name", type="string", example="Tea"),
     *             @OA\Property(property="description", type="string", example="Hot tea"),
     *             @OA\Property(property="price", type="number", example=30.00),
     *             @OA\Property(property="category", type="string", example="Beverage")
     *         )
     *     ),
     *     @OA\Response(response=201, description="POS item created")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:50',
        ]);

        return $this->posItemService->create($validated);
    }

    /**
     * @OA\Put(
     *     path="/api/pms/pos/items/{id}",
     *     tags={"POS Items"},
     *     summary="Update POS item",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="property_code", type="string", example="H001"),
     *             @OA\Property(property="name", type="string", example="Tea - Large"),
     *             @OA\Property(property="price", type="number", example=35.00)
     *         )
     *     ),
     *     @OA\Response(response=200, description="POS item updated")
     * )
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'category' => 'nullable|string|max:50',
        ]);

        return $this->posItemService->update($id, $validated);
    }

    /**
     * @OA\Delete(
     *     path="/api/pms/pos/items/{id}",
     *     tags={"POS Items"},
     *     summary="Delete POS item",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="POS item deleted")
     * )
     */
    public function destroy(Request $request, int $id)
    {
        return $this->posItemService->delete($id, $request->get('property_code'));
    }
}
