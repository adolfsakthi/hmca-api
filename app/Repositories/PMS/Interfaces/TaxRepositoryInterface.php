<?php

namespace App\Repositories\PMS\Interfaces;

interface TaxRepositoryInterface
{
    public function getAllByProperty(string $propertyCode);
    public function findByIdByProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update($tax, array $data);
    public function delete($tax);
    public function getActiveTaxes(string $propertyCode);
    public function findByName(string $name, string $propertyCode);
}
