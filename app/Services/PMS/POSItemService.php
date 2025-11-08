<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\POSItemRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class POSItemService
{
    protected $posItemRepository;

    public function __construct(POSItemRepositoryInterface $posItemRepository)
    {
        $this->posItemRepository = $posItemRepository;
    }

    public function getAll(string $propertyCode)
    {
        $items = $this->posItemRepository->getAllByProperty($propertyCode);
        return response()->json([
            'success' => true,
            'message' => 'POS items fetched successfully.',
            'data' => $items
        ], 200);
    }

    public function getById(int $id, string $propertyCode)
    {
        $item = $this->posItemRepository->findByIdByProperty($id, $propertyCode);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'POS item not found.'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'POS item fetched successfully.',
            'data' => $item
        ], 200);
    }

    public function create(array $data)
    {
        try {
            $item = $this->posItemRepository->create($data);
            return response()->json([
                'success' => true,
                'message' => 'POS item created successfully.',
                'data' => $item
            ], 201);
        } catch (Exception $e) {
            Log::error('POS item create failed: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create POS item.'], 500);
        }
    }

    public function update(int $id, array $data)
    {
        $item = $this->posItemRepository->findByIdByProperty($id, $data['property_code']);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'POS item not found.'], 404);
        }
        $updated = $this->posItemRepository->update($item, $data);
        return response()->json(['success' => true, 'message' => 'POS item updated successfully.', 'data' => $updated]);
    }

    public function delete(int $id, string $propertyCode)
    {
        $item = $this->posItemRepository->findByIdByProperty($id, $propertyCode);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'POS item not found.'], 404);
        }
        $this->posItemRepository->delete($item);
        return response()->json(['success' => true, 'message' => 'POS item deleted successfully.']);
    }
}
