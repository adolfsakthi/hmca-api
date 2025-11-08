<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PMS\TaxService;

class TaxController extends Controller
{
    protected $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * @OA\Get(
     *   path="/api/pms/taxes",
     *   tags={"Taxes"},
     *   summary="Get all taxes for a property",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="List of taxes")
     * )
     */
    public function index(Request $request)
    {
        return $this->taxService->getAllTaxes($request->get('property_code'));
    }

    /**
     * @OA\Post(
     *   path="/api/pms/taxes",
     *   tags={"Taxes"},
     *   summary="Create new tax",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"property_code","name","percentage"},
     *       @OA\Property(property="property_code", type="string", example="H001"),
     *       @OA\Property(property="name", type="string", example="CGST"),
     *       @OA\Property(property="percentage", type="number", example=9.0),
     *       @OA\Property(property="is_active", type="boolean", example=true)
     *     )
     *   ),
     *   @OA\Response(response=201, description="Tax created successfully")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'name' => 'required|string|max:50',
            'percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        return $this->taxService->createTax($validated);
    }

    /**
     * @OA\Put(
     *   path="/api/pms/taxes/{id}",
     *   tags={"Taxes"},
     *   summary="Update tax for property",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="property_code", type="string", example="H001"),
     *       @OA\Property(property="name", type="string", example="SGST"),
     *       @OA\Property(property="percentage", type="number", example=9.0),
     *       @OA\Property(property="is_active", type="boolean", example=true)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Tax updated successfully")
     * )
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'name' => 'sometimes|string|max:50',
            'percentage' => 'sometimes|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        return $this->taxService->updateTax($id, $validated);
    }

    /**
     * @OA\Delete(
     *   path="/api/pms/taxes/{id}",
     *   tags={"Taxes"},
     *   summary="Delete tax for property",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="property_code", in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Tax deleted successfully")
     * )
     */
    public function destroy(Request $request, int $id)
    {
        return $this->taxService->deleteTax($id, $request->get('property_code'));
    }
}
