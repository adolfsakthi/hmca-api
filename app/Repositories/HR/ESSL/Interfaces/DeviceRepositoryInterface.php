<?php

namespace App\Repositories\HR\ESSL\Interfaces;

interface DeviceRepositoryInterface
{
    public function listByProperty(string $propertyCode);
    public function findByIdAndProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update(int $id, string $propertyCode, array $data);
    public function delete(int $id, string $propertyCode): bool;
    public function getAlllogsByProperty(string $propertyCode);
}
