<?php

use D076\SanctumRefreshTokens\DTOs\LoginDTO;
use D076\SanctumRefreshTokens\Services\IAuthService;
use D076\SanctumRefreshTokens\Services\TokenService;
use D076\SanctumRefreshTokens\Tests\Fixtures\User;

describe('refresh-token TTL', function () {
    it('uses the long "remember" lifetime when remember is true', function () {
        makeUser('user@example.com', 'secret-password');

        $tokens = app(IAuthService::class)
            ->setCredentials(new LoginDTO('user@example.com', 'secret-password', User::class, remember: true))
            ->login();

        // sanctum.refresh_token_expiration = 43200 min (30 days)
        $minutes = abs(now()->diffInMinutes($tokens->refresh_token_expires_at));
        expect($minutes)->toBeGreaterThan(43000)->toBeLessThan(43300);
    });

    it('uses the short lifetime when remember is false', function () {
        makeUser('user@example.com', 'secret-password');

        $tokens = app(IAuthService::class)
            ->setCredentials(new LoginDTO('user@example.com', 'secret-password', User::class, remember: false))
            ->login();

        // sanctum.refresh_token_expiration_no_remember = 1440 min (1 day)
        $minutes = abs(now()->diffInMinutes($tokens->refresh_token_expires_at));
        expect($minutes)->toBeGreaterThan(1400)->toBeLessThan(1500);
    });

    it('preserves the original token lifetimes across a refresh (sliding window)', function () {
        $user = makeUser();

        $tokens = (new TokenService($user))->createTokens(
            accessTokenExpiresAt: now()->addMinutes(15),
            refreshTokenExpiresAt: now()->addMinutes(14400),
        );

        $new = app(IAuthService::class)->refresh($tokens->refresh_token);

        $accessMinutes = abs(now()->diffInMinutes($new->access_token_expires_at));
        $refreshMinutes = abs(now()->diffInMinutes($new->refresh_token_expires_at));

        // New pair keeps the original lifetimes (~15 min / ~14400 min), it does NOT
        // reset to the config defaults (60 min / 1440 min).
        expect($accessMinutes)->toBeGreaterThan(13)->toBeLessThan(17)
            ->and($refreshMinutes)->toBeGreaterThan(14300)->toBeLessThan(14500);
    });
});
