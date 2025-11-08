<?php

namespace App\Repositories\PMS\Interfaces;

interface POSItemRepositoryInterface
{
    public function getAllByProperty(string $propertyCode);
    public function findByIdByProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update($posItem, array $data);
    public function delete($posItem);
}
