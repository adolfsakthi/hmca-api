<?php

namespace App\Services\PMS;

use App\Repositories\PMS\Interfaces\UserRepositoryInterface;
use App\Repositories\SuperAdmin\Interfaces\PropertyRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected PropertyRepositoryInterface $propertyRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PropertyRepositoryInterface $propertyRepository
    ) {
        $this->userRepository = $userRepository;
        $this->propertyRepository = $propertyRepository;
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

        $users = $this->userRepository->getAllUserByProperty($property->id);

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

        $data['property_id'] = $property->id;
        $data['password'] = Hash::make($data['password']);


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
        $property = $this->propertyRepository->findByCode($data['propertyCode'] ?? null);
        if (!$property) {
            return [
                'success' => false,
                'message' => 'Invalid property code.',
                'data' => [],
            ];
        }

        $user = $this->userRepository->findByIdByProperty($id, $property->id);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
                'data' => [],
            ];
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

        $user = $this->userRepository->findByIdByProperty($id, $property->id);
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
