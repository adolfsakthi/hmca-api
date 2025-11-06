<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class ModuleAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Example usage:
     *   ->middleware('module.access:pms')
     */
    public function handle(Request $request, Closure $next, ...$modules)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $payload = JWTAuth::parseToken()->getPayload();

            $userModules = $payload->get('modules', []);

            $hasAccess = !empty(array_intersect($modules, $userModules));

            if ($payload->get('role') === 'super_admin') {
                return $next($request);
            }

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied: You do not have access to this module.'
                ], 403);
            }

            // if (!in_array($module, $modules)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => "Access denied: You don't have access to the '{$module}' module."
            //     ], 403);
            // }

            return $next($request);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized or invalid token.',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
