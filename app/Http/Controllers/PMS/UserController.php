<?php

namespace App\Http\Controllers\PMS;

use App\Http\Controllers\Controller;
use App\Services\PMS\UserService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="User Management",
 *     description="Manage PMS users"
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
     *     path="/api/users",
     *     tags={"User Management"},
     *     summary="Get all users",
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
     *     path="/api/users",
     *     tags={"User Management"},
     *     summary="Create a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","role_id"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="role_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="User created successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $validated['property_code'] = $request->get('property_code');

        $response = $this->userService->createUser($validated);
        $status = $response['success'] ? 201 : 400;

        return response()->json($response, $status);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"User Management"},
     *     summary="Update an existing user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Updated"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="newpassword"),
     *             @OA\Property(property="role_id", type="integer", example=2)
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
            'role_id' => 'sometimes|integer',
            'property_code' => 'required|string',
        ]);

        $response = $this->userService->updateUser($id, $validated);
        return response()->json($response, $response['success'] ? 200 : 400);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"User Management"},
     *     summary="Delete a user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer", example=10)
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
