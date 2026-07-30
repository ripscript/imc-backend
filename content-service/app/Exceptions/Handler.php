<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof \App\Exceptions\Domain\ValidationException) {
            return \App\Helpers\ApiResponse::error($e->getMessage(), null, $e->getCode());
        }

        if ($e instanceof \App\Exceptions\Domain\EntityNotFoundException) {
            return \App\Helpers\ApiResponse::error($e->getMessage(), null, $e->getCode());
        }

        if ($e instanceof \App\Exceptions\Domain\AuthenticationException) {
            return \App\Helpers\ApiResponse::error($e->getMessage(), null, $e->getCode());
        }

        if ($e instanceof \App\Exceptions\Domain\InvalidRoleException) {
            return \App\Helpers\ApiResponse::error($e->getMessage(), null, $e->getCode());
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
