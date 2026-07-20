<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class CheckJwtMultiGuard
{
    protected $guards = ['admin', 'pemohon'];

    public function handle(Request $request, Closure $next): Response
    {
        foreach ($this->guards as $guard) {
            try {
                if ($user = JWTAuth::parseToken()->authenticate()) {
                    return $next($request);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }
}
