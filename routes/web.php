<?php

use Illuminate\Support\Facades\Route;
use Lorisleiva\Actions\Facades\Actions;

Actions::registerRoutes('app/Actions/Exports');

Route::view('/{any?}', 'app')->where('any', '.*');
