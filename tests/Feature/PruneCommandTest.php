<?php

use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;

describe('sanctum:prune-refresh-expired', function () {
    it('prunes tokens expired beyond the retention window and keeps the rest', function () {
        $user = makeUser();

        $longExpired = $user->createRefreshToken(now()->subDays(5))->refreshToken;
        $recentlyExpired = $user->createRefreshToken(now()->subMinutes(30))->refreshToken;
        $active = $user->createRefreshToken(now()->addDay())->refreshToken;

        $this->artisan('sanctum:prune-refresh-expired --hours=24')->assertSuccessful();

        expect(PersonalRefreshToken::query()->find($longExpired->getKey()))->toBeNull()
            ->and(PersonalRefreshToken::query()->find($recentlyExpired->getKey()))->not->toBeNull()
            ->and(PersonalRefreshToken::query()->find($active->getKey()))->not->toBeNull();
    });

    it('prunes everything already expired when --hours=0', function () {
        $user = makeUser();

        $expired = $user->createRefreshToken(now()->subMinutes(5))->refreshToken;
        $active = $user->createRefreshToken(now()->addDay())->refreshToken;

        $this->artisan('sanctum:prune-refresh-expired --hours=0')->assertSuccessful();

        expect(PersonalRefreshToken::query()->find($expired->getKey()))->toBeNull()
            ->and(PersonalRefreshToken::query()->find($active->getKey()))->not->toBeNull();
    });
});
