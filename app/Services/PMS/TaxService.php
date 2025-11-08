<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\TaxRepositoryInterface;

class TaxService
{
    protected $taxRepository;

    public function __construct(TaxRepositoryInterface $taxRepository)
    {
        $this->taxRepository = $taxRepository;
    }

    public function getAllTaxes(string $propertyCode)
    {
        $taxes = $this->taxRepository->getAllByProperty($propertyCode);

        return response()->json([
            'success' => true,
            'message' => 'Taxes fetched successfully.',
            'data' => $taxes
        ], 200);
    }

    public function getTaxById(int $id, string $propertyCode)
    {
        $tax = $this->taxRepository->findByIdByProperty($id, $propertyCode);

        if (!$tax) {
            return response()->json([
                'success' => false,
                'message' => 'Tax not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tax fetched successfully.',
            'data' => $tax
        ], 200);
    }

    public function createTax(array $data)
    {
        $exists = $this->taxRepository->findByName($data['name'], $data['property_code']);

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'A tax with this name already exists for this property.'
            ], 409);
        }

        $tax = $this->taxRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tax created successfully.',
            'data' => $tax
        ], 201);
    }

    public function updateTax(int $id, array $data)
    {
        $tax = $this->taxRepository->findByIdByProperty($id, $data['property_code']);

        if (!$tax) {
            return response()->json([
                'success' => false,
                'message' => 'Tax not found.'
            ], 404);
        }

        $updatedTax = $this->taxRepository->update($tax, $data);

        return response()->json([
            'success' => true,
            'message' => 'Tax updated successfully.',
            'data' => $updatedTax
        ], 200);
    }

    public function deleteTax(int $id, string $propertyCode)
    {
        $tax = $this->taxRepository->findByIdByProperty($id, $propertyCode);

        if (!$tax) {
            return response()->json([
                'success' => false,
                'message' => 'Tax not found.'
            ], 404);
        }

        $deleted = $this->taxRepository->delete($tax);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Tax deleted successfully.' : 'Failed to delete tax.'
        ], $deleted ? 200 : 500);
    }
}
