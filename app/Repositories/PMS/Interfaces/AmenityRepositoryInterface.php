<?php

namespace App\Repositories\PMS\Interfaces;

use App\Models\PMS\Amenity;

interface AmenityRepositoryInterface
{
    public function findById(int $id): ?Amenity;

    public function findByIdByProperty(int $id, string $propertyCode): ?Amenity;

    public function getAll();

    public function getAllByProperty(string $propertyCode);

    public function create(array $data): Amenity;

    public function update(Amenity $amenity, array $data): Amenity;

    public function delete(Amenity $amenity): bool;

    public function findByPropertyCode(string $name, string $propertyCode): ?Amenity;
}
