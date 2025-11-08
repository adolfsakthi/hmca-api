<?php

namespace App\Repositories\PMS;

use App\Models\PMS\Tax;
use App\Repositories\PMS\Interfaces\TaxRepositoryInterface;

class TaxRepository implements TaxRepositoryInterface
{
    public function getAllByProperty(string $propertyCode)
    {
        return Tax::where('property_code', $propertyCode)
            ->orderBy('name')
            ->get();
    }

    public function findByIdByProperty(int $id, string $propertyCode)
    {
        return Tax::where('property_code', $propertyCode)->find($id);
    }

    public function create(array $data)
    {
        return Tax::create($data);
    }

    public function update($tax, array $data)
    {
        $tax->update($data);
        return $tax->fresh();
    }

    public function delete($tax)
    {
        return $tax->delete();
    }

    public function getActiveTaxes(string $propertyCode)
    {
        return Tax::where('property_code', $propertyCode)
            ->where('is_active', true)
            ->get();
    }

    public function findByName(string $name, string $propertyCode)
    {
        return Tax::where('property_code', $propertyCode)
            ->where('name', $name)
            ->first();
    }
}
