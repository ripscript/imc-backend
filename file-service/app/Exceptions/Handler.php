<?php

namespace App\Exceptions;

use App\Exceptions\Domain\EntityNotFoundException;
use App\Exceptions\Domain\ValidationException;
use App\Helpers\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException as LaravelValidationException;
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
        if ($e instanceof ValidationException) {
            return ApiResponse::error($e->getMessage(), $e->getErrors(), $e->getCode());
        }

        if ($e instanceof EntityNotFoundException) {
            return ApiResponse::error($e->getMessage(), null, 404);
        }

        if ($e instanceof AuthenticationException) {
            return ApiResponse::error('Unauthenticated', null, 401);
        }

        if ($e instanceof AuthorizationException) {
            return ApiResponse::error('Unauthorized', null, 403);
        }

        if ($e instanceof LaravelValidationException) {
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
