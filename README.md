# Laravel SanctumRefreshTokens

## Installation

```composer require d076/sanctum-refresh-tokens```

## Usage

1. Register SanctumRefreshServiceProvider in bootstrap/providers.php

2. Publish migrations ```php artisan vendor:publish --tag=sanctum-refresh-tokens```

3. Run migrations ```php artisan migrate```

4. Extend your User model from AuthenticatableUser
    ```php
    use D076\SanctumRefreshTokens\Models\AuthenticatableUser;
    
    class User extends AuthenticatableUser
    {
    }
    ```

5. Add prune commands to Schedule
    ```php
    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('sanctum:prune-expired --hours=0')->hourly();
    Schedule::command('sanctum:prune-refresh-expired --hours=0')->daily();
    ```

6. To create access and refresh tokens use TokenService
    ```php
    use D076\SanctumRefreshTokens\Services\TokenService;
    
    /** @var \D076\SanctumRefreshTokens\Models\AuthenticatableUser $user */
    (new TokenService($user))->createTokens();
    (new TokenService($user))->deleteCurrentTokens();
    ```

7. To change tokens expire time configure config/sanctum.php
    ```php
    'expiration' => env('SANCTUM_ACCESS_TOKEN_EXPIRATION', 60), // minutes
    'refresh_token_expiration' => env('SANCTUM_REFRESH_TOKEN_EXPIRATION', 43200), // minutes
    'refresh_token_expiration_no_remember' => env('SANCTUM_REFRESH_TOKEN_EXPIRATION_NO_REMEMBER', 1440), // minutes
    ```
