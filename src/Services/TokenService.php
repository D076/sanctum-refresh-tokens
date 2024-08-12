<?php

namespace D076\SanctumRefreshTokens\Services;

use D076\SanctumRefreshTokens\Enums\TokenType;
use D076\SanctumRefreshTokens\Helpers\ConfigHelper;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\NewAccessToken;
use D076\SanctumRefreshTokens\DTOs\TokensDTO;
use D076\SanctumRefreshTokens\Models\AuthenticatableUser;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use D076\SanctumRefreshTokens\NewRefreshToken;

class TokenService implements ITokenService
{
    protected NewAccessToken $token;

    protected NewRefreshToken $refreshToken;

    public function __construct(protected AuthenticatableUser $user)
    {
    }

    public function createTokens(
        ?Carbon $accessTokenExpiresAt = null,
        ?Carbon $refreshTokenExpiresAt = null,
        array $abilities = ['*']
    ): TokensDTO {
        $accessTokenExpiresAt ??= ConfigHelper::getAccessTokenExpiresAt();
        $refreshTokenExpiresAt ??= ConfigHelper::getRefreshTokenExpiresAt();

        $this->token = $this->user->createToken(
            TokenType::AccessToken->value,
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
            access_token_expires_at: $accessTokenExpiresAt,
            refresh_token_expires_at: $refreshTokenExpiresAt,
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

    public function deleteAllTokens(): void
    {
        $this->user->tokens()->where('name', '=', TokenType::AccessToken->value)->delete();
        $this->user->refreshTokens()->delete();
    }
}
