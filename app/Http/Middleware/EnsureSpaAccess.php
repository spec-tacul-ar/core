<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSpaAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->currentAccessToken()?->transient()) {
            return $next($request);
        }

        abort(403);
    }
}
