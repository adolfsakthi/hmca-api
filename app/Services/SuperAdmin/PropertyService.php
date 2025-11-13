<?php

namespace App\Services\SuperAdmin;

use App\Models\User;
use App\Repositories\SuperAdmin\Interfaces\PropertyRepositoryInterface;
use App\Repositories\PMS\UserRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Exists;

class PropertyService
{
    protected $propertyRepository;
    protected $userRepository;

    public function __construct(PropertyRepositoryInterface $propertyRepository, UserRepository $userRepository)
    {
        $this->propertyRepository = $propertyRepository;
        $this->userRepository = $userRepository;
    }

    public function getAllProperties()
    {
        $properties = $this->propertyRepository->findAll();

        return response()->json([
            'success' => true,
            'message' => 'Properties fetched successfully.',
            'data' => $properties
        ], 200);
    }

    public function getPropertyById($id)
    {
        $property = $this->propertyRepository->findById($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property fetched successfully.',
            'data' => $property
        ], 200);
    }

    public function createProperty(array $data)
    {

        try {
            return DB::transaction(function () use ($data) {

                if ($this->propertyRepository->findByCode($data['property_code'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Property code already exists.'
                    ], 409);
                }

                $property = $this->propertyRepository->create([
                    'property_name' => $data['property_name'],
                    'property_code' => $data['property_code'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'zip_code' => $data['zip_code'] ?? null,
                    'country' => $data['country'] ?? null,
                    'description' => $data['description'] ?? null,
                ]);

                $existingUser = $this->userRepository->findByEmail($data['email']);

                if (!$existingUser) {
                    $this->userRepository->create([
                        'name' => $property->property_name ?? 'Property Admin',
                        'email' => $data['email'],
                        'password' => Hash::make('Admin@123'),
                        'role_id' => 2,
                        'property_id' => $property->id,
                    ]);
                }

                // ✅ 5️⃣ Return success response
                return response()->json([
                    'success' => true,
                    'message' => 'Property created successfully.',
                    'data' => $property
                ], 201);
            });
        } catch (QueryException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Database error or duplicate entry.',
                'error' => $ex->getMessage()
            ], 500);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred while creating property.',
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function updateProperty($id, array $data)
    {
        $property = $this->propertyRepository->findById($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.'
            ], 404);
        }

        $updatedProperty = $this->propertyRepository->update($property, $data);

        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully.',
            'data' => $updatedProperty
        ], 200);
    }

    public function deleteProperty($id)
    {
        $property = $this->propertyRepository->findById($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.'
            ], 404);
        }

        $user = User::where('property_id', $property->id)->first();
        if ($user) {
            $user->delete();
            $deleted = $this->propertyRepository->delete($property);
            return response()->json([
                'success' => $deleted,
                'message' => $deleted
                    ? 'Property deleted successfully.'
                    : 'Failed to delete property.'
            ], $deleted ? 200 : 500);
        }
        return response()->json([
            'success' => false,
            'message' => 'Associated user not found. Cannot delete property.'
        ], 404);
    }

    public function temporaryDisableProperty($id)
    {
        $property = $this->propertyRepository->findById($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.'
            ], 404);
        }

        $updatedProperty = $this->propertyRepository->update($property, [
            'billing_active' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Property temporarily disabled successfully.',
            'data' => $updatedProperty
        ], 200);
    }

    public function getPropertyByIdForRole(int $id, $user)
    {
        $property = $this->propertyRepository->findById($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.'
            ], 404);
        }

        // 🧠 Property Admin can only view their own property
        if ($user->role->slug === 'admin' && $user->property_id !== $property->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this property.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property fetched successfully.',
            'data' => $property
        ], 200);
    }

    public function updatePropertyForRole(int $id, array $data, $user)
    {
        $property = $this->propertyRepository->findById($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.'
            ], 404);
        }

        if ($user->role->slug === 'admin' && $user->property_id !== $property->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this property.'
            ], 403);
        }

        $updatedProperty = $this->propertyRepository->update($property, $data);

        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully.',
            'data' => $updatedProperty
        ], 200);
    }
}
