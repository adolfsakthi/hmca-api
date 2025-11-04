<?php

namespace App\Repositories\SuperAdmin\Interfaces;

use App\Models\SuperAdmin\Property;

interface PropertyRepositoryInterface {
    public function create(array $data);
    public function delete(Property $property);
    public function update(Property $property, array $data);
    public function findAll();
    public function findById($id);
    public function findByCode(string $code);
    public function findByEmail(string $email);
}
