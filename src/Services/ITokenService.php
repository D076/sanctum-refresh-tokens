<?php

namespace D076\SanctumRefreshTokens\Services;

use D076\SanctumRefreshTokens\DTOs\TokensDTO;
use Illuminate\Support\Carbon;

interface ITokenService
{
    /**
     * @param  list<string>  $abilities
     */
    public function createTokens(
        ?Carbon $accessTokenExpiresAt = null,
        ?Carbon $refreshTokenExpiresAt = null,
        array $abilities = ['*']
    ): TokensDTO;

    public function deleteCurrentTokens(): void;

    public function deleteAllTokens(): void;
}
