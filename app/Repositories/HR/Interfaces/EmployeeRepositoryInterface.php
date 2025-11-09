<?php

namespace App\Repositories\HR\Interfaces;

interface EmployeeRepositoryInterface
{
    public function paginateByProperty(string $propertyCode, ?string $search, int $perPage = 15);
    public function findByIdAndProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update(int $id, string $propertyCode, array $data);
    public function delete(int $id, string $propertyCode): bool;
    public function existsByCode(string $propertyCode, string $employeeCode, ?int $ignoreId = null): bool;
}