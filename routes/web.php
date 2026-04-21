<?php

use App\Http\Controllers\AcceptInvitationController;
use Illuminate\Support\Facades\Route;
use Lorisleiva\Actions\Facades\Actions;

Actions::registerRoutes('app/Actions/Exports');

Route::get('invitations/{invitation}', AcceptInvitationController::class)
    ->middleware('signed')
    ->name('invitations.accept');

Route::view(config('spectacular.path') . '/{any?}', 'app')->where('any', '.*')->name('app');
