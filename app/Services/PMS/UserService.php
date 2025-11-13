<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\UserRepositoryInterface;
use App\Repositories\SuperAdmin\Interfaces\PropertyRepositoryInterface;
use App\Repositories\SuperAdmin\Interfaces\RoleRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected PropertyRepositoryInterface $propertyRepository;
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PropertyRepositoryInterface $propertyRepository,
        RoleRepositoryInterface $roleRepository
    ) {
        $this->userRepository = $userRepository;
        $this->propertyRepository = $propertyRepository;
        $this->roleRepository = $roleRepository;
    }

    public function getAllUser(string $propertyCode): array
    {
        $property = $this->propertyRepository->findByCode($propertyCode);

        if (!$property) {
            return [
                'success' => false,
                'message' => 'Property not found.',
                'data' => [],
            ];
        }

        $users = $this->userRepository->getAllUserByProperty($propertyCode);

        return [
            'success' => true,
            'message' => 'Users fetched successfully.',
            'data' => $users,
        ];
    }

    public function createUser(array $data): array
    {
        $property = $this->propertyRepository->findByCode($data['property_code'] ?? null);
        if (!$property) {
            return [
                'success' => false,
                'message' => 'Invalid property code.',
                'data' => [],
            ];
        }

        $role = $this->roleRepository->getRoleById($data['role_id'], $data['property_code']);
        if (!$role) {
            return [
                'success' => false,
                'message' => 'Invalid role selected.',
                'data' => [],
            ];
        }

        $data['password'] = Hash::make($data['password']);
        $data['role_id'] = $role->id;
        $user = $this->userRepository->create($data);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Failed to create user.',
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'message' => 'User created successfully.',
            'data' => $user,
        ];
    }

    public function updateUser(int $id, array $data): array
    {
        $property = $this->propertyRepository->findByCode($data['property_code'] ?? null);
        if (!$property) {
            return [
                'success' => false,
                'message' => 'Invalid property code.',
                'data' => [],
            ];
        }

        $user = $this->userRepository->findByIdByProperty($id, $data['property_code']);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'data' => [],
            ];
        }

        if (isset($data['role_id'])) {
            $role = $this->roleRepository->getRoleById($data['role_id'], $data['property_code']);
            if (!$role) {
                return [
                    'success' => false,
                    'message' => 'Invalid role selected.',
                    'data' => [],
                ];
            }

            $data['role_id'] = $role->id;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $updatedUser = $this->userRepository->update($user, $data);

        return [
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => $updatedUser,
        ];
    }

    public function deleteUser(int $id, string $propertyCode): array
    {
        $property = $this->propertyRepository->findByCode($propertyCode);
        if (!$property) {
            return [
                'success' => false,
                'message' => 'Invalid property code.',
                'data' => [],
            ];
        }

        $user = $this->userRepository->findByIdByProperty($id, $propertyCode);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'data' => [],
            ];
        }

        $this->userRepository->delete($user);

        return [
            'success' => true,
            'message' => 'User deleted successfully.',
            'data' => [],
        ];
    }
}
