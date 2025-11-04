<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\SuperAdmin\Interfaces\PropertyRepositoryInterface;
use App\Repositories\PMS\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    protected $userRepository;
    protected $propertyRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PropertyRepositoryInterface $propertyRepository
    ) {
        $this->userRepository = $userRepository;
        $this->propertyRepository = $propertyRepository;
    }

    public function login(array $data)
    {
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $propertyCode = $data['property_code'] ?? null;

        if (!$email || !$password || !$propertyCode) {
            return response()->json([
                'success' => false,
                'message' => 'Email, password, and property code are required',
            ], 422);
        }

        // Handle Super Admin Login
        if ($propertyCode === '000') {
            $user = User::where('email', $email)
                ->whereNull('property_id')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Super admin not found',
                ], 404);
            }
        } else {
            // Validate Property
            $property = $this->propertyRepository->findByCode($propertyCode);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid property code',
                ], 404);
            }

            // Find user for property
            $user = User::where('email', $email)
                ->where('property_id', $property->id)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for this property',
                ], 404);
            }
        }

        // Password check
        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Custom JWT claims
        $customClaims = [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'property_id' => $user->property_id,
            'property_code' => $propertyCode,
        ];

        // Generate JWT token
        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'property_id' => $user->property_id,
                'property_code' => $propertyCode,
                'token' => $token,
            ]
        ], 200);
    }


    public function refreshToken()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $newToken,
                ]
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
            ], 401);
        }
    }
}
