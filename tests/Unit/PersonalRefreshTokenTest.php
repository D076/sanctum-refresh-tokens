<?php

use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use D076\SanctumRefreshTokens\Services\TokenService;

it('stores the refresh token hashed, never in plaintext', function () {
    $user = makeUser();

    $tokens = (new TokenService($user))->createTokens();

    [$id, $plain] = explode('|', $tokens->refresh_token, 2);
    $row = PersonalRefreshToken::query()->findOrFail($id);

    expect($row->token)
        ->not->toBe($tokens->refresh_token)
        ->not->toContain($plain)
        ->toBe(hash('sha256', $plain));
});

it('finds a valid refresh token', function () {
    $user = makeUser();
    $tokens = (new TokenService($user))->createTokens();

    expect(PersonalRefreshToken::findToken($tokens->refresh_token))->not->toBeNull();
});

it('rejects a tampered refresh token', function () {
    $user = makeUser();
    $tokens = (new TokenService($user))->createTokens();

    expect(PersonalRefreshToken::findToken($tokens->refresh_token . 'tampered'))->toBeNull();
});

it('does not find an expired refresh token (TTL enforced)', function () {
    $user = makeUser();
    $tokens = (new TokenService($user))->createTokens(
        refreshTokenExpiresAt: now()->subMinute(),
    );

    expect(PersonalRefreshToken::findToken($tokens->refresh_token))->toBeNull();
});
