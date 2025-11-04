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
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = auth('api')->user();
        Log::info('Authenticated user in RoleMiddleware:', [
            'user' => $user,
            'token' => $request->bearerToken()
        ]);
        if (!$user) {
            Log::warning('No authenticated user found in RoleMiddleware.');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->role !== $role) {
            Log::warning('Role mismatch in RoleMiddleware.', [
                'expected' => $role,
                'actual' => $user->role,
            ]);
            return response()->json(['message' => 'Forbidden: Role does not have permission'], 403);
        }
        return $next($request);
    }
}
