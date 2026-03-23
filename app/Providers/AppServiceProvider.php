<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Feature;
use App\Models\Requirement;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
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
    }
}
