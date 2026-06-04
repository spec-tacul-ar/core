<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewAccount;
use App\Actions\Fortify\ResetAccountPassword;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewAccount::class);
        Fortify::resetUserPasswordsUsing(ResetAccountPassword::class);

        ResetPassword::createUrlUsing(fn($notifiable, $token) => url('app/password/reset', $token));
    }
}
