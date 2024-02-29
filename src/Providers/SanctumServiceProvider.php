<?php

namespace D076\SanctumRefreshTokens\Providers;

use Illuminate\Support\ServiceProvider;
use D076\SanctumRefreshTokens\Console\Commands\PruneRefreshExpired;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use D076\SanctumRefreshTokens\Observers\RefreshTokenObserver;

class SanctumServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        PersonalRefreshToken::observe([RefreshTokenObserver::class]);

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PruneRefreshExpired::class,
            ]);
        }
    }
}
