<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class RestrictedDocsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Gate::allows('viewApiDocs')) {
            return $next($request);
        }

        return response('Unauthorized', 401, [
            'WWW-Authenticate' => 'Basic realm="API Documentation"',
        ]);
    }
}
