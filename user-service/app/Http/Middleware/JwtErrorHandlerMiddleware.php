<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class JwtErrorHandlerMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        try {
            $auth = auth($guard);
            $token = JWTAuth::parseToken()->getToken();
            $user = $auth->setToken($token)->authenticate();
            if (! $user) {
                return ApiResponse::error('User not found', null, 404);
            }
            $request->attributes->set('guard', $guard);
        } catch (TokenExpiredException $e) {
            return ApiResponse::error('Token has expired', null, 401);
        } catch (TokenInvalidException $e) {
            return ApiResponse::error('Token is invalid', null, 401);
        } catch (TokenBlacklistedException $e) {
            return ApiResponse::error('Token is blacklisted', null, 401);
        } catch (JWTException $e) {
            return ApiResponse::error('Token is missing', null, 401);
        } catch (\Throwable $e) {
            return ApiResponse::error('JWT Auth failed', $e->getMessage(), 401);
        }

        return $next($request);
    }
}
