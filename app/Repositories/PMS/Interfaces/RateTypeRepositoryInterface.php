<?php

namespace App\Repositories\PMS\Interfaces;

interface RateTypeRepositoryInterface
{
    public function getAllByProperty(string $propertyCode);
    public function getByRoomIdAndProperty(int $roomId, string $propertyCode);
    public function findByIdByProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update($rateType, array $data);
    public function delete($rateType);
}
