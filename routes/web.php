<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('auth/check', [AuthController::class, 'check'])
    ->name('auth.check');

Route::get('exports/{project}/{type}', [ExportController::class, 'show'])
    ->name('exports.show')
    ->middleware('can:view,project');

Route::get('invitations/{invitation}', [InvitationController::class, 'accept'])
    ->name('invitations.accept')
    ->middleware('signed');

Route::controller(SocialiteController::class)
    ->middleware('guest')
    ->group(function () {
        $providers = array_keys(config('spectacular.socialite'));

        Route::get('auth/{provider}/redirect', 'redirect')
            ->whereIn('provider', $providers);

        Route::get('auth/{provider}/callback', 'callback')
            ->whereIn('provider', $providers);
    });

Route::view('app/{any?}', 'app')
    ->where('any', '.*')
    ->name('app');

Route::redirect('/', '/app');
