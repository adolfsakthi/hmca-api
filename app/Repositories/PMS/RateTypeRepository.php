<?php

namespace App\Repositories\PMS;

use App\Models\PMS\RateType;
use App\Models\PMS\Room;
use App\Repositories\PMS\Interfaces\RateTypeRepositoryInterface;

class RateTypeRepository implements RateTypeRepositoryInterface
{
    public function getAllByProperty(string $propertyCode)
    {
        return RateType::with('room_type')
            ->where('property_code', $propertyCode)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getByRoomIdAndProperty(int $roomId, string $propertyCode)
    {
        $room = Room::with('room_type.rate_types')
            ->where('property_code', $propertyCode)
            ->find($roomId);

        return $room?->room_type?->rate_types ?? collect([]);
    }

    public function findByIdByProperty(int $id, string $propertyCode)
    {
        return RateType::with('room_type')
            ->where('property_code', $propertyCode)
            ->find($id);
    }

    public function create(array $data)
    {
        return RateType::create($data);
    }

    public function update($rateType, array $data)
    {
        $rateType->update($data);
        return $rateType;
    }

    public function delete($rateType)
    {
        return $rateType->delete();
    }
}
