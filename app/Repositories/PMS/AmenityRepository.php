<?php

namespace App\Repositories\PMS;

use App\Models\PMS\Amenity;
use App\Repositories\PMS\Interfaces\AmenityRepositoryInterface;

class AmenityRepository implements AmenityRepositoryInterface
{
    public function findById(int $id): ?Amenity
    {
        return Amenity::find($id);
    }

    public function findByIdByProperty(int $id, string $propertyCode): ?Amenity
    {
        return Amenity::where('property_code', $propertyCode)
                      ->where('id', $id)
                      ->first();
    }

    public function getAll()
    {
        return Amenity::orderBy('id', 'desc')->all();
    }

    public function getAllByProperty(string $propertyCode)
    {
        return Amenity::where('property_code', $propertyCode)->orderBy('id', 'asc')->get();
    }

    public function create(array $data): Amenity
    {
        return Amenity::create($data);
    }

    public function update(Amenity $amenity, array $data): Amenity
    {
        $amenity->update($data);
        return $amenity;
    }

    public function delete(Amenity $amenity): bool
    {
        return $amenity->delete();
    }

    public function findByPropertyCode(string $name, string $propertyCode): ?Amenity
    {
        return Amenity::where('name', $name)
                      ->where('property_code', $propertyCode)
                      ->first();
    }
}
