<?php

namespace App\Services;

use App\Models\SuperAdmin\Module;
use App\Models\SuperAdmin\Property;
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
            $user = User::with('role')
                ->where('email', $email)
                ->whereNull('property_code')
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
            $user = User::with('role')->where('email', $email)
                ->where('property_code', $property->property_code)
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

        $modules = [];


        if ($user->role && $user->role?->slug === 'super-admin') {
            // Super admin gets access to all modules
            $modules = Module::pluck('code')->toArray();
        } elseif ($user->property_code) {
            // Property user: load assigned modules for that property
            $property = Property::with(['modules' => function ($query) {
                $query->wherePivot('enabled', true);
            }])
                ->where('property_code', $user->property_code) // ✅ use property_code instead of property_id
                ->first();
            $modules = $property ? $property->modules->pluck('code')->toArray() : [];
        } else {
            $modules = [];
        }

        // Custom JWT claims
        $customClaims = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->name,
            'property_id' => $user->property_id,
            'property_code' => $propertyCode,
            'modules' => $modules
        ];

        // Generate JWT token
        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role?->name,
                // 'property_id' => $property->id,
                'property_code' => $propertyCode,
                'modules' => $modules,
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
