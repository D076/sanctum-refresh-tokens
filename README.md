# Laravel Sanctum Refresh Tokens

[![Tests](https://github.com/d076/sanctum-refresh-tokens/actions/workflows/tests.yml/badge.svg)](https://github.com/d076/sanctum-refresh-tokens/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/d076/sanctum-refresh-tokens.svg)](https://packagist.org/packages/d076/sanctum-refresh-tokens)
[![License](https://img.shields.io/packagist/l/d076/sanctum-refresh-tokens.svg)](LICENSE)

Refresh tokens on top of [Laravel Sanctum](https://laravel.com/docs/sanctum). Sanctum
issues long-lived personal access tokens; this package adds a short-lived **access
token** paired with a longer-lived, single-use **refresh token**, so a client can
silently obtain a fresh pair without re-authenticating — the standard pattern for
SPAs and mobile apps.

## Features

- **Access + refresh token pairs** issued together, each with its own TTL.
- **Single-use rotation** — exchanging a refresh token deletes it and its bound
  access token, then issues a brand-new pair. Replaying a used token fails.
- **Hashed at rest** — refresh tokens are stored as SHA-256 hashes and compared in
  constant time (`hash_equals`); the plaintext is only ever returned to the client.
- **TTL enforced on lookup** — expired refresh tokens are never matched.
- **Credential login, logout and password reset** helpers that revoke the right
  tokens.
- **Override-friendly** — no routes or controllers are shipped; you wire your own.
  Services are bound behind interfaces, and the user model's email/password fields
  are configurable.
- **Prune command** for housekeeping expired tokens.

## Requirements

| | Version |
|---|---|
| PHP | `^8.3` |
| Laravel | `11`, `12`, `13` |
| Sanctum | `^4.0` |

Tested against PHP 8.3 / 8.4 / 8.5 and Laravel 11 / 12 / 13 on SQLite, PostgreSQL and MySQL.

## Installation

```bash
composer require d076/sanctum-refresh-tokens
```

Publish and run the migration (creates the `personal_refresh_tokens` table):

```bash
php artisan vendor:publish --tag=sanctum-refresh-tokens
php artisan migrate
```

This package builds on Sanctum's `personal_access_tokens` table, so make sure
Sanctum itself is installed and migrated (`php artisan install:api` on a fresh app).

### Upgrading from 3.x

4.0 adds an `abilities` column to `personal_refresh_tokens` (so a token's scope is
preserved across refreshes). Re-publish and migrate to pick it up:

```bash
php artisan vendor:publish --tag=sanctum-refresh-tokens
php artisan migrate
```

Refresh tokens issued before the upgrade have no stored scope and fall back to `['*']`
on their next refresh.

## Setup

Extend your authenticatable model from `AuthenticatableUser`:

```php
use D076\SanctumRefreshTokens\Models\AuthenticatableUser;

class User extends AuthenticatableUser
{
    // ...
}
```

`AuthenticatableUser` already pulls in Sanctum's `HasApiTokens` plus this package's
refresh-token behaviour. If you can't change your base class, use the trait directly
and implement the contract instead:

```php
use D076\SanctumRefreshTokens\HasApiTokensInterface;
use D076\SanctumRefreshTokens\Traits\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasApiTokensInterface
{
    use HasApiTokens;
}
```

## Configuration

Token lifetimes are read from Sanctum's config (`config/sanctum.php`). Add the keys
this package uses alongside Sanctum's own:

```php
// config/sanctum.php
'expiration' => env('SANCTUM_ACCESS_TOKEN_EXPIRATION', 60),                          // access token, minutes
'refresh_token_expiration' => env('SANCTUM_REFRESH_TOKEN_EXPIRATION', 43200),        // refresh "remember me", minutes (30 days)
'refresh_token_expiration_no_remember' => env('SANCTUM_REFRESH_TOKEN_EXPIRATION_NO_REMEMBER', 1440), // refresh without remember, minutes (1 day)
```

If a key is absent the built-in defaults above are used, so the package works
out of the box. The optional `sanctum.token_prefix` is honoured for refresh tokens
too (useful for secret-scanning).

## Usage

The package ships **no routes or controllers** — you stay in control of your API
surface. Inject the services where you need them.

### Issuing tokens

```php
use D076\SanctumRefreshTokens\Services\TokenService;

$tokens = (new TokenService($user))->createTokens();
```

`createTokens()` accepts optional overrides:

```php
$tokens = (new TokenService($user))->createTokens(
    accessTokenExpiresAt: now()->addMinutes(15),
    refreshTokenExpiresAt: now()->addDays(7),
    abilities: ['orders:read', 'orders:write'],
);
```

It returns a `TokensDTO`:

```php
$tokens->access_token;              // plain-text bearer token
$tokens->refresh_token;             // plain-text refresh token ("{id}|{token}")
$tokens->token_type;                // "Bearer"
$tokens->access_token_expires_at;   // Carbon|null
$tokens->refresh_token_expires_at;  // Carbon|null
$tokens->model;                     // morph class of the user
$tokens->user;                      // the user model

return response()->json($tokens);   // implements Arrayable
```

### Logging in with credentials

```php
use D076\SanctumRefreshTokens\DTOs\LoginDTO;
use D076\SanctumRefreshTokens\Services\IAuthService;

public function login(Request $request, IAuthService $auth)
{
    $credentials = new LoginDTO(
        email: $request->input('email'),
        password: $request->input('password'),
        model: User::class,
        remember: $request->boolean('remember'),
    );

    // throws Illuminate\Auth\AuthenticationException on bad credentials
    $tokens = $auth->setCredentials($credentials)->login();

    return response()->json($tokens);
}
```

`remember` selects between the two refresh-token TTLs (`refresh_token_expiration`
vs `refresh_token_expiration_no_remember`).

Already have the user (e.g. social login)? Skip credentials:

```php
$tokens = $auth->setUser($user)->login();
```

### Refreshing

```php
public function refresh(Request $request, IAuthService $auth)
{
    // throws AuthenticationException if the token is unknown, expired or already used
    $tokens = $auth->refresh($request->input('refresh_token'));

    return response()->json($tokens);
}
```

The old refresh token **and its bound access token** are deleted before the new
pair is issued, so a stolen-and-replayed token is rejected on the second use.

### Logout

Behind the `auth:sanctum` guard, the authenticated request carries the current
access token, so logout can revoke exactly that pair:

```php
public function logout(Request $request, IAuthService $auth)
{
    $auth->setUser($request->user())->logout();

    return response()->noContent();
}
```

### Password reset

Hashes the new password, saves it, and revokes **all** of the user's access and
refresh tokens:

```php
$auth->setUser($user)->resetPassword($newPassword);
```

### Revoking tokens directly

```php
use D076\SanctumRefreshTokens\Services\TokenService;

(new TokenService($user))->deleteCurrentTokens(); // current pair only
(new TokenService($user))->deleteAllTokens();      // every pair
```

## Pruning expired tokens

A console command removes refresh tokens that expired more than `--hours` ago
(default 24):

```bash
php artisan sanctum:prune-refresh-expired --hours=0
```

Schedule it next to Sanctum's own pruning:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('sanctum:prune-expired --hours=0')->hourly();
Schedule::command('sanctum:prune-refresh-expired --hours=0')->daily();
```

## Customisation

### Custom email / password columns

If your model doesn't use `email` / `password`, expose the column names and the
package will pick them up:

```php
class User extends AuthenticatableUser
{
    public function getEmailField(): string
    {
        return 'login';
    }

    public function getPasswordField(): string
    {
        return 'pass_hash';
    }
}
```

### Swapping the service implementations

Both services are bound behind interfaces, so you can rebind your own in a service
provider:

```php
use D076\SanctumRefreshTokens\Services\IAuthService;
use D076\SanctumRefreshTokens\Services\ITokenService;

$this->app->bind(IAuthService::class, MyAuthService::class);
$this->app->bind(ITokenService::class, MyTokenService::class);
```

## How it works

- A refresh token is `{id}|{token}`, where `{token}` is 40 random characters plus a
  CRC32b checksum (and the optional `sanctum.token_prefix`).
- Only `SHA-256({token})` is stored in `personal_refresh_tokens.token`; the column is
  hidden from serialization.
- `PersonalRefreshToken::findToken()` looks up the row by id, verifies the hash with
  `hash_equals()`, and only matches rows whose `expires_at` is in the future.
- Each refresh token is linked to the access token it was issued with
  (`access_token_id`); deleting the refresh token cascades to that access token via a
  model observer.
- The token's abilities (scope) are stored on the refresh token row, so refreshing
  preserves the scope even after the short-lived access token has been pruned.

## Testing

```bash
composer install
composer test      # Pest
composer analyse   # PHPStan (level 6)
```

A Docker setup is included to run the suite (including the PostgreSQL/MySQL matrix):

```bash
docker compose run --rm test composer install
docker compose run --rm test vendor/bin/pest

# cross-database
docker compose up -d --wait pgsql mysql
docker compose run --rm -e DB_DRIVER=pgsql test vendor/bin/pest --group=cross-db
docker compose run --rm -e DB_DRIVER=mysql test vendor/bin/pest --group=cross-db
```

## Changelog & License

See [CHANGELOG.md](CHANGELOG.md). Released under the [MIT license](LICENSE).
