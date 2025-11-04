<?php

namespace App\Repositories\PMS;

use App\Models\PMS\RoomType;
use App\Repositories\PMS\Interfaces\RoomTypeRepositoryInterface;

class RoomTypeRepository implements RoomTypeRepositoryInterface
{
    public function findById(int $id): ?RoomType
    {
        return RoomType::find($id);
    }

    public function findByIdByProperty(int $id, string $propertyCode): ?RoomType
    {
        return RoomType::where('property_code', $propertyCode)
            ->where('id', $id)
            ->first();
    }

    public function getAll()
    {
        return RoomType::all();
    }

    public function getAllByProperty(string $propertyCode)
    {
        return RoomType::where('property_code', $propertyCode)->get();
    }

    public function create(array $data)
    {
        return RoomType::create($data);
    }

    public function update(RoomType $roomType, array $data): RoomType
    {
        $roomType->update($data);
        return $roomType;
    }

    public function delete(RoomType $roomType): bool
    {
        return $roomType->delete();
    }

    public function findByPropertyCode(string $name, string $propertyCode): ?RoomType
    {
        return RoomType::where('name', $name)
            ->where('property_code', $propertyCode)
            ->first();
    }
}
