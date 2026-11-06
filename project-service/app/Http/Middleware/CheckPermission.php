<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CheckPermission
{
    public function handle($request, Closure $next, ...$permissions)
    {
        // 1. Feature flag: if disabled, false, null, or empty string, immediately bypass
        $enabled = config('app.enable_rbac_permission', env('ENABLE_RBAC_PERMISSION', false));
        if (! filter_var($enabled, FILTER_VALIDATE_BOOLEAN)) {
            return $next($request);
        }

        // 2. Authenticate user
        $guard = $request->attributes->get('guard', config('auth.defaults.guard'));
        $user = Auth::guard($guard)->user();

        if (! $user) {
            return ApiResponse::error('Unauthorized', '', 401);
        }

        // 3. Superadmin always bypasses all permission checks
        $roleCode = null;
        try {
            $payload = Auth::guard($guard)->payload();
            if ($payload && $payload->hasKey('role')) {
                $roleCode = $payload->get('role');
            }
        } catch (\Throwable $e) {
            // JWT payload parsing exception
        }
        if (! $roleCode && $user->role) {
            $roleCode = $user->role->code;
        }

        if ($roleCode === 'super_admin') {
            return $next($request);
        }

        // 4. Retrieve permissions strictly from JWT claims (zero-query, no database lookup)
        $userPermissions = [];
        try {
            $payload = Auth::guard($guard)->payload();
            if ($payload && $payload->hasKey('permissions')) {
                $userPermissions = (array) $payload->get('permissions');
            }
        } catch (\Throwable $e) {
            try {
                $payload = JWTAuth::parseToken()->getPayload();
                if ($payload && $payload->hasKey('permissions')) {
                    $userPermissions = (array) $payload->get('permissions');
                }
            } catch (\Throwable $e2) {
                // No JWT payload or permissions claim found
            }
        }

        // 5. Evaluate permissions
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions, true)) {
                $hasPermission = true;
                break;
            }
        }

        if (! $hasPermission) {
            return ApiResponse::error('Forbidden - Insufficient permissions', '', 403);
        }

        return $next($request);
    }
}
