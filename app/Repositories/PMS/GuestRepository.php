<?php

namespace App\Repositories\PMS;

use App\Models\PMS\Guest;
use App\Repositories\PMS\Interfaces\GuestRepositoryInterface;

class GuestRepository implements GuestRepositoryInterface
{
    public function getAllByProperty(string $propertyCode)
    {
        return Guest::where('property_code', $propertyCode)
            ->latest()
            ->get();
    }

    public function findByIdByProperty(int $id, string $propertyCode)
    {
        return Guest::where('property_code', $propertyCode)->find($id);
    }

    public function create(array $data)
    {
        return Guest::create($data);
    }

    public function update($guest, array $data)
    {
        $guest->update($data);
        return $guest->fresh();
    }

    public function delete($guest)
    {
        return $guest->delete();
    }

    public function findByMobileOrEmail(string $propertyCode, ?string $mobile = null, ?string $email = null)
    {
        $query = Guest::where('property_code', $propertyCode);
        if ($mobile) {
            $query->where('mobile_no', $mobile);
        }
        if ($email) {
            $query->orWhere('email', $email);
        }
        return $query->first();
    }
}
