<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SuperAdmin\UserService;
use App\Services\AuthService;

/**
 * @OA\Tag(
 *     name="Authentication",
 * )
 */
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     operationId="login",
     *     tags={"Authentication"},
     *     security={},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(
     *             required={"email","password","property_code"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="property_code", type="string", example="PROP001")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *     ),
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'property_code' => 'required|string',
        ]);

        $response = $this->authService->login($request->only(['email', 'password', 'property_code']));

        return $response;
    }

    /**
     * @OA\Post(
     *     path="/api/refresh-token",
     *     summary="Refresh JWT token",
     *     operationId="refreshToken",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="New token generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="new.jwt.token.here")
     *         )
     *     ),
     * )
     */
    public function refreshToken()
    {
        return $this->authService->refreshToken();
    }
}
