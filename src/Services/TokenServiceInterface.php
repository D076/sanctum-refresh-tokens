<?php

namespace D076\SanctumRefreshTokens\Services;

use D076\SanctumRefreshTokens\DTOs\TokensDTO;

interface TokenServiceInterface
{
    public function createTokens(): TokensDTO;

    public function deleteCurrentTokens(): void;
}
