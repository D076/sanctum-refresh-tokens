<?php

use D076\SanctumRefreshTokens\Services\IAuthService;
use D076\SanctumRefreshTokens\Services\TokenService;

describe('revocation is scoped to a single user', function () {
    it('deleteAllTokens only affects the target user', function () {
        $alice = makeUser('alice@example.com');
        $bob = makeUser('bob@example.com');

        (new TokenService($alice))->createTokens();
        (new TokenService($alice))->createTokens();
        (new TokenService($bob))->createTokens();

        (new TokenService($alice))->deleteAllTokens();

        expect($alice->tokens()->count())->toBe(0)
            ->and($alice->refreshTokens()->count())->toBe(0)
            ->and($bob->tokens()->count())->toBe(1)
            ->and($bob->refreshTokens()->count())->toBe(1);
    });

    it('resetPassword only revokes the target user\'s tokens', function () {
        $alice = makeUser('alice@example.com');
        $bob = makeUser('bob@example.com');

        (new TokenService($alice))->createTokens();
        (new TokenService($bob))->createTokens();

        app(IAuthService::class)->setUser($alice)->resetPassword('a-new-password');

        expect($alice->refreshTokens()->count())->toBe(0)
            ->and($bob->tokens()->count())->toBe(1)
            ->and($bob->refreshTokens()->count())->toBe(1);
    });
});
