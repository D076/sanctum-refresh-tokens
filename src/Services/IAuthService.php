<?php

namespace D076\SanctumRefreshTokens\Services;

use D076\SanctumRefreshTokens\DTOs\LoginDTO;
use D076\SanctumRefreshTokens\DTOs\TokensDTO;
use D076\SanctumRefreshTokens\Models\AuthenticatableUser;

interface IAuthService
{
    public function login(): TokensDTO;

    public function logout(): void;

    public function refresh(string $refreshToken): TokensDTO;

    public function setCredentials(LoginDTO $loginDTO): self;

    public function setUser(AuthenticatableUser $user): self;

    public function resetPassword(string $password): self;
}
