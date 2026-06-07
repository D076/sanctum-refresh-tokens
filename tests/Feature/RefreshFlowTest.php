<?php

use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use D076\SanctumRefreshTokens\Services\IAuthService;
use D076\SanctumRefreshTokens\Services\TokenService;
use Illuminate\Auth\AuthenticationException;
use Laravel\Sanctum\PersonalAccessToken;

it('issues a working new token pair on refresh', function () {
    $user = makeUser();
    $tokens = (new TokenService($user))->createTokens();

    $new = app(IAuthService::class)->refresh($tokens->refresh_token);

    expect($new->refresh_token)
        ->not->toBe($tokens->refresh_token)
        ->and(PersonalRefreshToken::findToken($new->refresh_token))->not->toBeNull();
});

it('deletes the old refresh and its access token on rotation', function () {
    $user = makeUser();
    $tokens = (new TokenService($user))->createTokens();

    $old = PersonalRefreshToken::findToken($tokens->refresh_token);
    $oldAccessId = $old->access_token_id;

    app(IAuthService::class)->refresh($tokens->refresh_token);

    expect(PersonalRefreshToken::findToken($tokens->refresh_token))->toBeNull()
        ->and(PersonalAccessToken::query()->find($oldAccessId))->toBeNull();
});

it('rejects replay of an already-used refresh token', function () {
    $user = makeUser();
    $tokens = (new TokenService($user))->createTokens();

    app(IAuthService::class)->refresh($tokens->refresh_token);
    app(IAuthService::class)->refresh($tokens->refresh_token);
})->throws(AuthenticationException::class);

it('rejects refresh with an unknown token', function () {
    app(IAuthService::class)->refresh('999|nonexistent-token');
})->throws(AuthenticationException::class);

it('persists the scope on the refresh token itself', function () {
    $user = makeUser();

    $tokens = (new TokenService($user))->createTokens(abilities: ['orders:read']);

    $refreshToken = PersonalRefreshToken::findToken($tokens->refresh_token);
    expect($refreshToken->abilities)->toBe(['orders:read']);
});

it('preserves the abilities across a refresh even after the access token is gone', function () {
    $user = makeUser();

    $tokens = (new TokenService($user))->createTokens(abilities: ['orders:read']);

    // Simulate the short-lived access token being pruned before the client refreshes
    // (the normal case: access TTL is minutes, refresh TTL is days).
    PersonalAccessToken::findToken($tokens->access_token)->delete();

    $new = app(IAuthService::class)->refresh($tokens->refresh_token);

    // The refreshed token keeps its scope instead of widening to ['*'].
    $newPat = PersonalAccessToken::findToken($new->access_token);
    expect($newPat->abilities)->toBe(['orders:read'])
        ->and($newPat->can('orders:read'))->toBeTrue()
        ->and($newPat->can('orders:write'))->toBeFalse();
});
