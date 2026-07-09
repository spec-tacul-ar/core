<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Feature;
use App\Models\Requirement;
use App\Models\Scopes\WithoutHistoryScope;
use App\Policies\TokenPolicy;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WithoutHistoryScope::class);
    }

    public function boot(): void
    {
        Auth::provider('eloquent', function ($app, array $config) {
            return new class ($app['hash'], $config['model']) extends EloquentUserProvider {
                public function retrieveById($identifier)
                {
                    try {
                        return $this->createModel()->resolveRouteBinding($identifier);
                    } catch (ModelNotFoundException $exception) {
                        return null;
                    }
                }
            };
        });

        Blueprint::macro('revisionable', function (): void {
            $this->softDeletes();
            $this->uuid('uuid')->unique();
            $this->binary('history')->nullable();
        });

        Passport::authorizationView(function ($parameters) {
            return view('mcp.authorize', $parameters);
        });

        Passport::cookie('spectacular_token');

        Gate::policy(Token::class, TokenPolicy::class);

        Relation::enforceMorphMap([
            'account' => Account::class,
            'feature' => Feature::class,
            'requirement' => Requirement::class,
        ]);

        // Don't let the HTTP Host header dictate URLs.
        URL::useOrigin(config('app.url'));
    }
}
