<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class JwtErrorHandlerMiddleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = count($guards) ? $guards : [null];

        $lastError = null;

        foreach ($guards as $guard) {
            try {
                $auth = auth($guard);
                $token = JWTAuth::parseToken()->getToken();
                $user = $auth->setToken($token)->authenticate();

                if ($user) {
                    Auth::shouldUse($guard);

                    if ($guard === 'pemohon') {
                        $payload = JWTAuth::getPayload($token);

                        if (
                            ! isset($payload['token_version']) ||
                            (int) $payload['token_version'] !== (int) $user->token_version
                        ) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Sesi telah berakhir. Silakan login kembali.',
                            ], 401);
                        }
                    }

                    $request->attributes->set('guard', $guard);

                    return $next($request);
                }
            } catch (TokenExpiredException $e) {
                $lastError = ApiResponse::error('Token has expired', 401);
            } catch (TokenInvalidException $e) {
                $lastError = ApiResponse::error('Token is invalid', 401);
            } catch (TokenBlacklistedException $e) {
                $lastError = ApiResponse::error('Token is blacklisted', 401);
            } catch (JWTException $e) {
                $lastError = ApiResponse::error('Token is missing', 401);
            } catch (Exception $e) {
                $lastError = ApiResponse::error('JWT Auth failed', $e->getMessage(), 401);
            }
        }

        return $lastError ?? ApiResponse::error('Unauthorized', 401);
    }
}
