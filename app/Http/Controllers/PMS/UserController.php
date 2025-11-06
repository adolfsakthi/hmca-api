<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Services\PMS\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="User Management",
 *     description="Manage users in the PMS system"
 * )
 */
class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/api/pms/users",
     *     tags={"User Management"},
     *     summary="Get all users by property code",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Users fetched successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        $response = $this->userService->getAllUser($propertyCode);
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/pms/users",
     *     tags={"User Management"},
     *     summary="Create a new user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","role","property_code"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="role", type="string", example="manager"),
     *         )
     *     ),
     *     @OA\Response(response=201, description="User created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
        ]);


        $response = $this->userService->createUser($validated);
        $status = $response['success'] ? 201 : 400;

        return response()->json($response, $status);
    }

    /**
     * @OA\Put(
     *     path="/api/pms/users/{id}",
     *     tags={"User Management"},
     *     summary="Update an existing user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="User ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(property="role", type="string", example="admin"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="User updated successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|required|string',
            'propertyCode' => 'required|string',
        ]);

        $response = $this->userService->updateUser($id, $validated);
        return response()->json($response, $response['success'] ? 200 : 400);
    }

    /**
     * @OA\Delete(
     *     path="/api/pms/users/{id}",
     *     tags={"User Management"},
     *     summary="Delete a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="User ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_code"},
     *             @OA\Property(property="property_code", type="string", example="PROP001")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User deleted successfully"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function destroy(Request $request, int $id)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
        ]);
        $response = $this->userService->deleteUser($id, $validated['property_code']);
        return response()->json($response, $response['success'] ? 200 : 400);
    }
}
