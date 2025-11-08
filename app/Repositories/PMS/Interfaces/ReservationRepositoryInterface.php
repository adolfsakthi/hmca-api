<?php

namespace App\Repositories\PMS\Interfaces;

interface ReservationRepositoryInterface
{
    public function getAllByProperty(string $propertyCode);
    public function findByIdByProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update($reservation, array $data);
    public function delete($reservation);
    public function getActiveReservations(string $propertyCode);
    public function getBetweenDates(string $propertyCode, $from, $to);
}
