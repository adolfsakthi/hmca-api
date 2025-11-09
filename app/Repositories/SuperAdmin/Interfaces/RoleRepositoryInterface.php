<?php

namespace App\Repositories\SuperAdmin\Interfaces;

use App\Models\SuperAdmin\Role;

interface RoleRepositoryInterface
{
    public function getAllRoles(string $propertyCode);
    public function getRoleById(int $id,string $propertyCode): ?Role;
    public function createRole(array $data): Role;
    public function updateRole(int $id, array $data): bool;
    public function deleteRole(int $id): bool;
}
