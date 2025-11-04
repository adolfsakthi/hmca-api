<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class InjectPropertyCode
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $tokenPropertyCode = JWTAuth::parseToken()->getPayload()->get('property_code');
            if ($tokenPropertyCode) {
                $request->merge(['property_code' => $tokenPropertyCode]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing token',
                'error' => $e->getMessage()
            ], 401);
        }
        return $next($request);
    }
}
