<?php

namespace App\Repositories\PMS\Interfaces;

use App\Models\PMS\Reservation;

interface ReservationRepositoryInterface
{
    public function getAllbyProperty(string $propertyCode);
    public function getByProperty(string $id, string $propertyCode);
    public function getAll();
    public function find(string $id);
    public function create(array $data);
    public function update(Reservation $reservation, array $data);
    public function delete(Reservation $reservation);
}
