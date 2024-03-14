<?php

namespace D076\SanctumRefreshTokens\Providers;

use Illuminate\Support\ServiceProvider;
use D076\SanctumRefreshTokens\Console\Commands\PruneRefreshExpired;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use D076\SanctumRefreshTokens\Observers\RefreshTokenObserver;

class SanctumRefreshServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        PersonalRefreshToken::observe([RefreshTokenObserver::class]);

        if ($this->app->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ], 'sanctum-refresh-migrations');

            $this->commands([
                PruneRefreshExpired::class,
            ]);
        }
    }
}
