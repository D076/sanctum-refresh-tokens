<?php

use D076\SanctumRefreshTokens\Services\AuthService;
use D076\SanctumRefreshTokens\Services\IAuthService;
use D076\SanctumRefreshTokens\Services\ITokenService;
use D076\SanctumRefreshTokens\Services\TokenService;
use D076\SanctumRefreshTokens\Tests\Fixtures\User;

it('binds the auth service contract', function () {
    expect(app(IAuthService::class))->toBeInstanceOf(AuthService::class);
});

it('binds the token service contract with a user', function () {
    $service = app(ITokenService::class, ['user' => makeUser()]);

    expect($service)->toBeInstanceOf(TokenService::class);
})->skip(fn () => ! class_exists(User::class), 'fixture missing');

it('registers the prune command', function () {
    $this->artisan('sanctum:prune-refresh-expired --hours=0')->assertSuccessful();
});
