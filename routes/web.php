<?php

use App\Http\Controllers\AcceptInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('invitations/{invitation}', AcceptInvitationController::class)
    ->middleware('signed')
    ->name('invitations.accept');

Route::view(config('spectacular.path') . '/{any?}', 'app')->where('any', '.*')->name('app');
