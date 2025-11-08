<?php

namespace App\Repositories\PMS\Interfaces;

interface GuestRepositoryInterface
{
    public function getAllByProperty(string $propertyCode);
    public function findByIdByProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update($guest, array $data);
    public function delete($guest);
    public function findByMobileOrEmail(string $propertyCode, ?string $mobile = null, ?string $email = null);
}
