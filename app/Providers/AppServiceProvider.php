<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Feature;
use App\Models\Requirement;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceHttps(app()->environment(['production', 'staging']));

        Relation::enforceMorphMap([
            'account' => Account::class,
            'feature' => Feature::class,
            'requirement' => Requirement::class,
        ]);

        Gate::before(function (Account $account, string $ability) {
            if (!$account->exists) {
                return true;
            }
        });

        Auth::viaRequest('solo', function (Request $request) {
            // Do not provide the solo user if there are any accounts.
            if (Account::exists()) {
                return null;
            }

            return new Account([
                'id' => 0,
                'name' => 'Default',
                'email' => 'solo@spectacular',
            ]);
        });
    }
}
