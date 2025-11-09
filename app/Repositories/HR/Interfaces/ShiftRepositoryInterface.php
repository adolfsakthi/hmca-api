<?php

namespace App\Repositories\HR\Interfaces;

interface ShiftRepositoryInterface
{
    public function listByProperty(string $propertyCode, ?int $perPage = null, ?string $search = null);
    public function findByIdAndProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update(int $id, string $propertyCode, array $data);
    public function delete(int $id, string $propertyCode): bool;
    public function existsByCode(string $propertyCode, string $code, ?int $ignoreId = null): bool;
}
