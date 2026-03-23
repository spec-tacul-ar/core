<?php

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveLocalUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('spectacular.mode') === 'solo') {
            auth()->setUser(new Account([
                'id' => 0,
                'name' => 'Solo User',
                'email' => 'solo@example.test',
            ]));
        }

        return $next($request);
    }
}
