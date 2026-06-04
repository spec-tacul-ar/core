<?php

use App\Http\Middleware\DecodeSqids;
use App\Http\Middleware\MakeFolioPagesCachable;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['sqids' => DecodeSqids::class]);
        $middleware->prependToGroup('web', MakeFolioPagesCachable::class);
        $middleware->redirectGuestsTo(fn() => url('app/login'));

        $middleware->web(append: [
            CreateFreshApiToken::class, // Make sure this is always appended last.
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
