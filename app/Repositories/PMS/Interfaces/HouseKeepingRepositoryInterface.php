<?php

namespace App\Repositories\PMS\Interfaces;

interface HousekeepingRepositoryInterface
{
    public function getAllByProperty(string $propertyCode);
    public function findByIdByProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update($housekeeping, array $data);
    public function delete($housekeeping);
}
