<?php

namespace App\Repositories\HR\Interfaces;

interface LeaveTypeRepositoryInterface
{
    public function create(array $data);
    public function update(int $id, string $propertyCode, array $data);
    public function delete(int $id, string $propertyCode): bool;
    public function findByIdAndProperty(int $id, string $propertyCode);
    public function listByProperty(string $propertyCode);
}
