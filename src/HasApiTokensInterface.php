<?php

namespace D076\SanctumRefreshTokens;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use Laravel\Sanctum\Contracts\HasApiTokens;

interface HasApiTokensInterface extends HasApiTokens
{
    public function refreshTokens(): MorphMany;

    public function createRefreshToken(?DateTimeInterface $expiresAt = null, ?int $accessTokenId = null): NewRefreshToken;

    public function withRefreshToken(?PersonalRefreshToken $refreshToken): static;
}
