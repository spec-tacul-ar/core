<?php

use Illuminate\Support\Facades\Route;
use Lorisleiva\Actions\Facades\Actions;

Route::middleware(['auth:api', 'verified'])->group(function () {
    Actions::registerRoutes();
});
