<?php

namespace App\Repositories\PMS\Interfaces;

use App\Models\PMS\RoomType;

interface RoomTypeRepositoryInterface
{
    public function findById(int $id): ?RoomType;

    public function findByIdByProperty(int $id, string $propertyCode): ?RoomType;

    public function getAll();

    public function getAllByProperty(string $propertyCode);

    public function create(array $data);

    public function update(RoomType $roomType, array $data): RoomType;

    public function delete(RoomType $roomType): bool;

    public function findByPropertyCode(string $name, string $propertyCode);
}
