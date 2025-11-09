<?php

namespace App\Repositories\HR\Interfaces;

interface DutyRosterRepositoryInterface
{
    public function listForWeek(string $propertyCode, string $weekStartDate);
    public function findByIdAndProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update(int $id, string $propertyCode, array $data);
    public function delete(int $id, string $propertyCode): bool;
    public function upsertForEmployeeDate(string $propertyCode, int $employeeId, string $date, array $data);
}
