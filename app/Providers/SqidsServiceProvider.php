<?php

namespace App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Sqids\Sqids;
use Sqids\SqidsInterface;

class SqidsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SqidsInterface::class, function (Application $app) {
            return new Sqids(
                alphabet: config('spectacular.sqids.alphabet'),
                minLength: config('spectacular.sqids.length'),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
