<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureSpaAccess;
use Lorisleiva\Actions\Facades\Actions;

Route::middleware(['auth:api', EnsureSpaAccess::class, 'verified'])->group(function () {
    Actions::registerRoutes();
});
