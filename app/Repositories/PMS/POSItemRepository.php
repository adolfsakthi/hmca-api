<?php

namespace App\Repositories\PMS;

use App\Models\PMS\POSItem;
use App\Repositories\PMS\Interfaces\POSItemRepositoryInterface;

class POSItemRepository implements POSItemRepositoryInterface
{
    public function getAllByProperty(string $propertyCode)
    {
        return POSItem::where('property_code', $propertyCode)->get();
    }

    public function findByIdByProperty(int $id, string $propertyCode)
    {
        return POSItem::where('id', $id)
            ->where('property_code', $propertyCode)
            ->first();
    }

    public function create(array $data)
    {
        return POSItem::create($data);
    }

    public function update($posItem, array $data)
    {
        $posItem->update($data);
        return $posItem;
    }

    public function delete($posItem)
    {
        return $posItem->delete();
    }
}
