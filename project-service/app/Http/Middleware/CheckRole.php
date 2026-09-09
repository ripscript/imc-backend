<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        $guard = $request->attributes->get('guard', config('auth.defaults.guard'));
        $user = Auth::guard($guard)->user();

        if (! $user || ! $user->role) {
            return ApiResponse::error('Unauthorized', '', 403);
        }

        if (! in_array($user->role->code, $roles)) {
            return ApiResponse::error('Access Denied', '', 403);
        }

        return $next($request);
    }
}
