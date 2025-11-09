<?php

namespace App\Services\SuperAdmin;

use App\Repositories\SuperAdmin\Interfaces\RoleRepositoryInterface;

class RoleService
{
    protected $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function listRoles(string $propertyCode): array
    {
        $roles = $this->roleRepository->getAllRoles($propertyCode);

        return [
            'success' => true,
            'message' => 'Roles retrieved successfully.',
            'data' => $roles,
        ];
    }

    /**
     * Create a new role for a property
     */
    public function create(array $data): array
    {
        $role = $this->roleRepository->createRole($data);

        return [
            'success' => true,
            'message' => 'Role created successfully.',
            'data' => $role,
        ];
    }

    /**
     * Get single role by ID and property
     */
    public function show(int $id, string $propertyCode): array
    {
        $role = $this->roleRepository->getRoleById($id, $propertyCode);

        if (!$role) {
            return [
                'success' => false,
                'message' => 'Role not found for this property.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Role retrieved successfully.',
            'data' => $role,
        ];
    }

    /**
     * Update role by ID and property
     */
    public function update(int $id, array $data): array
    {

        $role = $this->roleRepository->getRoleById($id, $data['property_code']);

        if (!$role) {
            return [
                'success' => false,
                'message' => 'Role not found for this property.',
                'data' => null,
            ];
        }

        $role->update($data);

        return [
            'success' => true,
            'message' => 'Role updated successfully.',
            'data' => $role,
        ];
    }

    /**
     * Delete role by ID and property
     */
    public function delete(int $id, string $propertyCode): array
    {
        $role = $this->roleRepository->getRoleById($id, $propertyCode);

        if (!$role) {
            return [
                'success' => false,
                'message' => 'Role not found for this property.',
                'data' => null,
            ];
        }

        $role->delete();

        return [
            'success' => true,
            'message' => 'Role deleted successfully.',
            'data' => null,
        ];
    }
}
