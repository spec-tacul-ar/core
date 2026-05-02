<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MakeFolioPagesCachable
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->route()->named('laravel-folio')) {
            return $next($request);
        }

        $response = $next($request);

        $response->headers->remove('Set-Cookie');
        $response->headers->set('Cache-Control', 'public, max-age=' . 60 * 60);

        return $response;
    }
}
