<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExportController;
use App\Http\Middleware\TidyHtml;
use Illuminate\Support\Facades\Route;

Route::controller(ExportController::class)->group(function () {
    Route::get('export/{project}/html', 'html')->name('export.html')->middleware(TidyHtml::class);
    Route::get('export/{project}/markdown', 'markdown')->name('export.markdown');
    Route::get('export/{project}/json', 'json')->name('export.json');
});

Route::controller(AuthController::class)->middleware('guest')->group(function () {
    Route::get('auth/{provider}/redirect', 'redirect')->whereIn('provider', auth_providers());
    Route::get('auth/{provider}/callback', 'callback')->whereIn('provider', auth_providers());
});

Route::view('/{any?}', 'app')->where('any', '.*');
