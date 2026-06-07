<?php

namespace D076\SanctumRefreshTokens\Tests;

use D076\SanctumRefreshTokens\Providers\SanctumRefreshServiceProvider;
use Laravel\Sanctum\SanctumServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Default `users` table.
        $this->loadLaravelMigrations();

        // Sanctum's `personal_access_tokens` — Sanctum 4 ships but does not
        // auto-register its migration (Laravel 11+ publishes it via install:api),
        // so load it explicitly from the installed package for the suite.
        $this->loadMigrationsFrom(__DIR__ . '/../vendor/laravel/sanctum/database/migrations');

        // This package's `personal_refresh_tokens` (package only publishes it).
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            SanctumServiceProvider::class,
            SanctumRefreshServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Use $app['config']->set(...), NOT the config() facade — facade isn't ready yet.
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        // DB_DRIVER selects the connection: sqlite (default, no container needed) or
        // pgsql/mysql for the cross-db job. The pgsql/mysql settings read env so the
        // same suite runs against the CI service containers.
        $driver = env('DB_DRIVER', 'sqlite');
        $app['config']->set('database.default', $driver);

        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('database.connections.pgsql', [
            'driver' => 'pgsql',
            'host' => env('PG_HOST', '127.0.0.1'),
            'port' => (int) env('PG_PORT', 5432),
            'database' => env('PG_DATABASE', 'testing'),
            'username' => env('PG_USERNAME', 'sanctum'),
            'password' => env('PG_PASSWORD', 'secret'),
            'charset' => 'utf8',
            'prefix' => '',
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]);

        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('MYSQL_HOST', '127.0.0.1'),
            'port' => (int) env('MYSQL_PORT', 3306),
            'database' => env('MYSQL_DATABASE', 'testing'),
            'username' => env('MYSQL_USERNAME', 'sanctum'),
            'password' => env('MYSQL_PASSWORD', 'secret'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);

        // Custom expiration keys this package reads from the sanctum config.
        $app['config']->set('sanctum.expiration', 60);
        $app['config']->set('sanctum.refresh_token_expiration', 43200);
        $app['config']->set('sanctum.refresh_token_expiration_no_remember', 1440);
    }
}
