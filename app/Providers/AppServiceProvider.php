<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Feature;
use App\Models\Requirement;
use Illuminate\Database\Schema\Blueprint;
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
        Auth::viaRequest('solo', function (Request $request) {
            if (config('spectacular.mode') === 'solo') {
                return new Account([
                    'id' => 0,
                    'name' => 'Solo User',
                    'email' => 'solo@spectacular',
                    'email_verified_at' => now(),
                ]);
            }
        });

        Blueprint::macro('revisionable', function (): void {
            $this->softDeletes();
            $this->uuid('uuid')->unique();
            $this->binary('history')->nullable();
        });

        Gate::before(function (Account $account) {
            // Allow solo users to bypass policies
            if (config('spectacular.mode') === 'solo') {
                return true;
            }
        });

        Relation::enforceMorphMap([
            'account' => Account::class,
            'feature' => Feature::class,
            'requirement' => Requirement::class,
        ]);
    }
}
