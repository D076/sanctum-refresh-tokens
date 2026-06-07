<?php

use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use D076\SanctumRefreshTokens\Services\IAuthService;
use D076\SanctumRefreshTokens\Services\TokenService;
use Laravel\Sanctum\PersonalAccessToken;

describe('logout / deleteCurrentTokens', function () {
    it('revokes only the current token pair, leaving the user\'s other sessions intact', function () {
        $user = makeUser();

        $session1 = (new TokenService($user))->createTokens();
        $session2 = (new TokenService($user))->createTokens();

        // Authenticate the request as session 1's access token (what Sanctum does
        // behind the auth:sanctum guard) so logout can target that exact pair.
        $currentAccessToken = PersonalAccessToken::findToken($session1->access_token);
        $user->withAccessToken($currentAccessToken);

        app(IAuthService::class)->setUser($user)->logout();

        // Session 1 is gone…
        expect(PersonalAccessToken::findToken($session1->access_token))->toBeNull()
            ->and(PersonalRefreshToken::findToken($session1->refresh_token))->toBeNull()
            // …session 2 survives.
            ->and(PersonalAccessToken::findToken($session2->access_token))->not->toBeNull()
            ->and(PersonalRefreshToken::findToken($session2->refresh_token))->not->toBeNull()
            ->and($user->tokens()->count())->toBe(1)
            ->and($user->refreshTokens()->count())->toBe(1);
    });

    it('does not touch another user\'s tokens on logout', function () {
        $alice = makeUser('alice@example.com');
        $bob = makeUser('bob@example.com');

        $aliceSession = (new TokenService($alice))->createTokens();
        (new TokenService($bob))->createTokens();

        $alice->withAccessToken(PersonalAccessToken::findToken($aliceSession->access_token));
        app(IAuthService::class)->setUser($alice)->logout();

        expect($bob->tokens()->count())->toBe(1)
            ->and($bob->refreshTokens()->count())->toBe(1);
    });
});
