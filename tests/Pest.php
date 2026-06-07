<?php

use D076\SanctumRefreshTokens\Tests\Fixtures\User;
use D076\SanctumRefreshTokens\Tests\TestCase;
use Illuminate\Support\Facades\Hash;

// The whole suite is also tagged `cross-db` so the cross-database CI job can run
// it verbatim against PostgreSQL and MySQL (`pest --group=cross-db`).
uses(TestCase::class)->group('cross-db')->in(__DIR__ . '/Feature', __DIR__ . '/Unit');

/**
 * Create a persisted test user.
 */
function makeUser(string $email = 'user@example.com', string $password = 'secret-password'): User
{
    return User::query()->create([
        'name' => 'Test User',
        'email' => $email,
        'password' => Hash::make($password),
    ]);
}
