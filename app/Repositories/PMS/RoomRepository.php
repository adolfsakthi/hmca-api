<?php

namespace App\Repositories\PMS;

use App\Models\PMS\Room;
use App\Repositories\PMS\Interfaces\RoomRepositoryInterface;

class RoomRepository implements RoomRepositoryInterface
{
    public function findById(int $id): ?Room
    {
        return Room::with(['roomType', 'amenities'])->find($id);
    }

    public function findByIdByProperty(int $id, string $propertyCode): ?Room
    {
        return Room::with(['roomType', 'amenities'])
            ->where('property_code', $propertyCode)
            ->where('id', $id)
            ->first();
    }

    public function getAll()
    {
        return Room::with(['roomType', 'amenities'])->get();
    }

    public function getAllByProperty(string $propertyCode)
    {
        return Room::with(['roomType', 'amenities'])
            ->where('property_code', $propertyCode)
            ->get();
    }

    public function create(array $data): Room
    {
        return Room::create($data);
    }

    public function update(Room $room, array $data): Room
    {
        $room->update($data);
        return $room;
    }

    public function delete(Room $room): bool
    {
        return $room->delete();
    }

    public function findByPropertyCode(string $room_number, string $propertyCode): ?Room
    {
        return Room::where('room_number', $room_number)
            ->where('property_code', $propertyCode)
            ->first();
    }

    public function updateStatus(int $roomId, string $status)
    {
        $room = Room::find($roomId);
        if (!$room) {
            return null;
        }

        $room->update(['status' => $status]);
        return $room;
    }
}
