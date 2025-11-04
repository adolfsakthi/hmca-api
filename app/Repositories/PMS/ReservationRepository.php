<?php

namespace App\Repositories\PMS;

use App\Repositories\PMS\Interfaces\ReservationRepositoryInterface;
use App\Models\PMS\Reservation;

class ReservationRepository implements ReservationRepositoryInterface
{

    public function getAllbyProperty(string $propertyCode)
    {
        return Reservation::with('room')
            ->where('property_code', $propertyCode)
            ->latest()
            ->get();
    }

    public function getByProperty(string $id, string $propertyCode)
    {
        return Reservation::with('room')
            ->where('property_code', $propertyCode)
            ->where('id', $id)
            ->first();
    }

    public function getAll()
    {
        return Reservation::with('room')->latest()->get();
    }

    public function find(string $id)
    {
        return Reservation::with('room')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Reservation::create($data);
    }

    public function update(Reservation $reservation, array $data)
    {
        $reservation->update($data);
        return $reservation;
    }

    public function delete(Reservation $reservation): bool
    {
        $reservation->delete();
        return true;
    }
}
