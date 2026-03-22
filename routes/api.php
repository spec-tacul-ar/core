<?php

use Illuminate\Support\Facades\Route;
use Lorisleiva\Actions\Facades\Actions;

Route::middleware(['auth:sanctum'])->group(function () {
    Actions::registerRoutes();
});
