<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use app\Models\User;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role  Role required to access
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // $user = auth('api')->user();
        // Log::info('Authenticated user in RoleMiddleware:', [
        //     'user' => $user,
        //     'token' => $request->bearerToken()
        // ]);
        // if (!$user) {
        //     Log::warning('No authenticated user found in RoleMiddleware.');
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        // if ($user->role !== $role) {
        //     Log::warning('Role mismatch in RoleMiddleware.', [
        //         'expected' => $role,
        //         'actual' => $user->role,
        //     ]);
        //     return response()->json(['message' => 'Forbidden: Role does not have permission'], 403);
        // }
        // $request->merge(['auth_user' => $user]);
        // return $next($request);
        $user = auth('api')->user();

        Log::info('Authenticated user in RoleMiddleware:', [
            'user_id' => $user?->id,
            'role' => $user?->role,
            'token' => $request->bearerToken()
        ]);

        // 🧱 1️⃣ Check authentication
        if (!$user) {
            Log::warning('No authenticated user found in RoleMiddleware.');
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // 🧱 2️⃣ Check role access (supports multiple allowed roles)
        if (!in_array($user->role, $roles)) {
            Log::warning('Role mismatch in RoleMiddleware.', [
                'allowed_roles' => $roles,
                'actual_role' => $user->role,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Your role does not have permission.'
            ], 403);
        }

        // 🧱 3️⃣ Attach authenticated user to the request for later use
        $request->merge(['auth_user' => $user]);

        // 🧱 4️⃣ Continue the request
        return $next($request);
    }
}
