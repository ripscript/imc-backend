<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
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

        return ApiResponse::error('Unauthorized', null, 401);
    }
}
