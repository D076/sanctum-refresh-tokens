<?php

use D076\SanctumRefreshTokens\DTOs\LoginDTO;
use D076\SanctumRefreshTokens\Services\IAuthService;
use D076\SanctumRefreshTokens\Services\TokenService;
use D076\SanctumRefreshTokens\Tests\Fixtures\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

it('logs in with valid credentials and issues a token pair', function () {
    makeUser('login@example.com', 'pw-123456');

    $tokens = app(IAuthService::class)
        ->setCredentials(new LoginDTO('login@example.com', 'pw-123456', User::class))
        ->login();

    expect($tokens->access_token)->not->toBeEmpty()
        ->and($tokens->refresh_token)->not->toBeEmpty()
        ->and($tokens->token_type)->toBe('Bearer');
});

it('rejects invalid credentials', function () {
    makeUser('login@example.com', 'pw-123456');

    app(IAuthService::class)
        ->setCredentials(new LoginDTO('login@example.com', 'wrong-password', User::class))
        ->login();
})->throws(AuthenticationException::class);

it('deletes all access and refresh tokens', function () {
    $user = makeUser();
    (new TokenService($user))->createTokens();
    (new TokenService($user))->createTokens();

    (new TokenService($user))->deleteAllTokens();

    expect($user->refreshTokens()->count())->toBe(0)
        ->and($user->tokens()->count())->toBe(0);
});

it('resets password and revokes all tokens', function () {
    $user = makeUser('reset@example.com', 'old-password');
    (new TokenService($user))->createTokens();

    app(IAuthService::class)->setUser($user)->resetPassword('new-password');

    $user->refresh();

    expect(Hash::check('new-password', $user->password))->toBeTrue()
        ->and($user->refreshTokens()->count())->toBe(0);
});
