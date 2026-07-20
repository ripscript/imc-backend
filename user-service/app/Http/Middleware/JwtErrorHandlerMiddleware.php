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
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }
            if ($guard === 'pemohon') {
                $payload = JWTAuth::getPayload($token);

                if (
                    ! isset($payload['token_version']) ||
                    (int) $payload['token_version'] !== (int) $user->token_version
                ) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Sesi telah berakhir. Silakan login kembali.',
                    ], 401);
                }
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
