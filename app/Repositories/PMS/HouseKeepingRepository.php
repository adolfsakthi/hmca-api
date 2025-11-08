<?php


namespace App\Repositories\PMS;

use App\Models\PMS\Housekeeping;
use App\Repositories\PMS\Interfaces\HousekeepingRepositoryInterface;

class HousekeepingRepository implements HousekeepingRepositoryInterface
{
    public function getAllByProperty(string $propertyCode)
    {
        return Housekeeping::where('property_code', $propertyCode)->with('room')->get();
    }

    public function findByIdByProperty(int $id, string $propertyCode)
    {
        return Housekeeping::where('id', $id)
            ->where('property_code', $propertyCode)
            ->first();
    }

    public function create(array $data)
    {
        return Housekeeping::create($data);
    }

    public function update($housekeeping, array $data)
    {
        $housekeeping->update($data);
        return $housekeeping;
    }

    public function delete($housekeeping)
    {
        return $housekeeping->delete();
    }
}
