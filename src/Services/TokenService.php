<?php

namespace D076\SanctumRefreshTokens\Services;

use Laravel\Sanctum\NewAccessToken;
use D076\SanctumRefreshTokens\DTOs\TokensDTO;
use D076\SanctumRefreshTokens\Enums\TokenType;
use D076\SanctumRefreshTokens\Models\AuthenticatableUser;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use D076\SanctumRefreshTokens\NewRefreshToken;

class TokenService implements TokenServiceInterface
{
    protected NewAccessToken $token;

    protected NewRefreshToken $refreshToken;

    public function __construct(protected AuthenticatableUser $user)
    {
    }

    public function createTokens(array $abilities = ['*']): TokensDTO
    {
        [$accessTokenExpiresAt, $refreshTokenExpiresAt] = $this->getExpiresAt();

        $this->token = $this->user->createToken(
            TokenType::ACCESS_TOKEN->value,
            $abilities,
            $accessTokenExpiresAt,
        );

        $this->refreshToken = $this->user->createRefreshToken(
            $refreshTokenExpiresAt,
            $this->token->accessToken?->id,
        );

        return new TokensDTO(
            model: $this->user->getMorphClass(),
            token_type: 'Bearer',
            access_token: $this->token->plainTextToken,
            refresh_token: $this->refreshToken->plainTextToken,
            access_token_expires_at: $accessTokenExpiresAt?->timestamp,
            refresh_token_expires_at: $refreshTokenExpiresAt?->timestamp,
            user: $this->user,
        );
    }

    public function deleteCurrentTokens(): void
    {
        if ($this->user->currentAccessToken()) {
            PersonalRefreshToken::query()
                ->where('access_token_id', $this->user->currentAccessToken()->id)
                ->delete();
            $this->user->currentAccessToken()->delete();
        }
    }

    protected function getExpiresAt(): array
    {
        return [
            now()->addMinutes(config('sanctum.expiration', 1440)),
            now()->addMinutes(config('sanctum.refresh_token_expiration', 43200)),
        ];
    }
}
