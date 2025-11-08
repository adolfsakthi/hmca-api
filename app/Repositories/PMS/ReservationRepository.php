<?php

namespace App\Repositories\PMS;

use App\Models\PMS\Reservation;
use App\Repositories\PMS\Interfaces\ReservationRepositoryInterface;

class ReservationRepository implements ReservationRepositoryInterface
{
    public function getAllByProperty(string $propertyCode)
    {
        return Reservation::with(['guest', 'room'])
            ->where('property_code', $propertyCode)
            ->latest()
            ->get();
    }

    public function findByIdByProperty(int $id, string $propertyCode)
    {
        return Reservation::with(['guest', 'room'])
            ->where('property_code', $propertyCode)
            ->find($id);
    }

    public function create(array $data)
    {
        return Reservation::create($data);
    }

    public function update($reservation, array $data)
    {
        $reservation->update($data);
        return $reservation->fresh(['guest', 'room']);
    }

    public function delete($reservation)
    {
        return $reservation->delete();
    }

    public function getActiveReservations(string $propertyCode)
    {
        return Reservation::with(['guest', 'room'])
            ->where('property_code', $propertyCode)
            ->where('status', 'checked_in')
            ->get();
    }

    public function getBetweenDates(string $propertyCode, $from, $to)
    {
        return Reservation::with(['guest', 'room'])
            ->where('property_code', $propertyCode)
            ->whereBetween('check_in', [$from, $to])
            ->get();
    }
}
