<?php

namespace App\Repositories\PMS\Interfaces;

interface UserRepositoryInterface
{
    public function getAllUserByProperty(string $propertyCode);
    public function findByIdByProperty(int $id, string $propertyCode);
    public function create(array $data);
    public function update($user, array $data);
    public function delete($user);
    public function findAll();
    public function findById($id);
    public function findByEmail(string $email);
}
