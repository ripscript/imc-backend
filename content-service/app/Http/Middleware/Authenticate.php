<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards)
    {
        abort(ApiResponse::error('Unauthenticated', null, 401));
    }
}
