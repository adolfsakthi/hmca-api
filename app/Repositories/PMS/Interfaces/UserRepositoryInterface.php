<?php

namespace App\Repositories\PMS\Interfaces;

interface UserRepositoryInterface
{
    public function getAllUserByProperty(int $propertyCode);
    public function findByIdByProperty(int $id, $propertyCode);
    public function create(array $data);
    public function update($user, array $data);
    public function delete($user);
    public function findAll();
    public function findById($id);
}
