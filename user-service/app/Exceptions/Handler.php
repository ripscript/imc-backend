<?php

namespace App\Exceptions;

use App\Exceptions\Domain\AccountBlockedException;
use App\Exceptions\Domain\AuthenticationException;
use App\Exceptions\Domain\EntityNotFoundException;
use App\Exceptions\Domain\InvalidRoleException;
use App\Exceptions\Domain\OtpException;
use App\Exceptions\Domain\RateLimitException;
use App\Exceptions\Domain\ValidationException as DomainValidationException;
use App\Helpers\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof DomainValidationException) {
            return ApiResponse::error($e->getMessage(), $e->getErrors(), $e->getCode());
        }

        if ($e instanceof EntityNotFoundException) {
            return ApiResponse::error($e->getMessage(), null, $e->getCode());
        }

        if ($e instanceof AuthenticationException) {
            return ApiResponse::error($e->getMessage(), null, $e->getCode());
        }

        if ($e instanceof AccountBlockedException) {
            return ApiResponse::error($e->getMessage(), null, $e->getCode());
        }

        if ($e instanceof InvalidRoleException) {
            return ApiResponse::error($e->getMessage(), null, $e->getCode());
        }

        if ($e instanceof OtpException) {
            return ApiResponse::error($e->getMessage(), null, $e->getCode());
        }

        if ($e instanceof RateLimitException) {
            return ApiResponse::error($e->getMessage(), null, $e->getCode());
        }

        if ($e instanceof ValidationException) {
            $errors = $e->errors();
            $flatErrors = collect($errors)->flatten()->implode(', ');

            return ApiResponse::error('Validation failed', $flatErrors, 422);
        }

        if ($e instanceof ModelNotFoundException) {
            return ApiResponse::error('Resource not found', null, 404);
        }

        $status = $e instanceof HttpExceptionInterface
            ? $e->getStatusCode()
            : 500;

        if ($status === 500) {
            return ApiResponse::error('Something went wrong', $e->getMessage(), 500);
        }

        $message = $e->getMessage() ?: 'Unexpected error';

        return ApiResponse::error($message, null, $status);
    }
}
