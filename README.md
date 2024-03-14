# Laravel SanctumRefreshTokens

## Installation

```composer require d076/sanctum-refresh-tokens```

## Usage

1. Publish migrations ```php artisan vendor:publish --tag=sanctum-refresh-migrations```

2. Run migrations ```php artisan migrate```

3. Extend your User model from AuthenticatableUser
    ```php
    use D076\SanctumRefreshTokens\Models\AuthenticatableUser;
    
    class User extends AuthenticatableUser
    {
    }
    ```

4. Add prune commands to Kernel
    ```php
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sanctum:prune-expired --hours=0')->hourly();
        $schedule->command('sanctum:prune-refresh-expired --hours=0')->daily();
    }
    ```

5. To create access and refresh tokens use TokenService
    ```php
    use D076\SanctumRefreshTokens\Services\TokenService;
    
    /** @var \D076\SanctumRefreshTokens\Models\AuthenticatableUser $user */
    (new TokenService($user))->createTokens();
    (new TokenService($user))->deleteCurrentTokens();
    ```

6. To change tokens expire time configure config/sanctum.php
    ```php
    'expiration' => env('SANCTUM_ACCESS_TOKEN_EXPIRATION', 1440), // minutes
    'refresh_token_expiration' => env('SANCTUM_REFRESH_TOKEN_EXPIRATION', 43200), // minutes
    ```
