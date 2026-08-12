<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class ThrottleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('mcp-oauth-registration', function (Request $request) {
            if (! $request->is('oauth/register')) {
                return Limit::none();
            }

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(25)->by($request->ip()),
            ];
        });
    }
}
