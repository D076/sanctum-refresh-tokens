<?php

namespace D076\SanctumRefreshTokens\Providers;

use D076\SanctumRefreshTokens\Services\AuthService;
use D076\SanctumRefreshTokens\Services\IAuthService;
use D076\SanctumRefreshTokens\Services\TokenService;
use D076\SanctumRefreshTokens\Services\ITokenService;
use Illuminate\Support\ServiceProvider;
use D076\SanctumRefreshTokens\Console\Commands\PruneRefreshExpired;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use D076\SanctumRefreshTokens\Observers\RefreshTokenObserver;

class SanctumRefreshServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        app()->bind(ITokenService::class, TokenService::class);
        app()->bind(IAuthService::class, AuthService::class);
    }

    public function boot(): void
    {
        PersonalRefreshToken::observe([RefreshTokenObserver::class]);

        if ($this->app->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ], 'sanctum-refresh-tokens');

            $this->commands([
                PruneRefreshExpired::class,
            ]);
        }
    }
}
