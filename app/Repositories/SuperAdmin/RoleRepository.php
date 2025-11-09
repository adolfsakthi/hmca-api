<?php

namespace App\Repositories\SuperAdmin;

use App\Models\SuperAdmin\Role;
use App\Repositories\SuperAdmin\Interfaces\RoleRepositoryInterface;

class RoleRepository implements RoleRepositoryInterface
{
    public function getAllRoles(string $propertyCode)
    {
        return Role::where('property_code', $propertyCode)->get();
    }

    public function getRoleById(int $id, string $propertyCode): ?Role
    {
        return Role::where('property_code', $propertyCode)
            ->where('id', $id)
            ->first();
    }

    public function createRole(array $data): Role
    {
        return Role::create($data);
    }

    public function updateRole(int $id, array $data): bool
    {
        $role = Role::find($id);
        if (!$role) return false;
        return $role->update($data);
    }

    public function deleteRole(int $id): bool
    {
        $role = Role::find($id);
        if (!$role) return false;
        return $role->delete();
    }
}
