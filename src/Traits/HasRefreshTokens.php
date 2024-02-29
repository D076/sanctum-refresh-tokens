<?php

namespace D076\SanctumRefreshTokens\Traits;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use D076\SanctumRefreshTokens\NewRefreshToken;

trait HasRefreshTokens
{
    protected ?PersonalRefreshToken $refreshToken;

    /**
     * Get the access tokens that belong to model.
     */
    public function refreshTokens(): MorphMany
    {
        return $this->morphMany(PersonalRefreshToken::class, 'tokenable');
    }

    public function createRefreshToken(?DateTimeInterface $expiresAt = null, ?int $accessTokenId = null): NewRefreshToken
    {
        $plainTextToken = sprintf(
            '%s%s%s',
            config('sanctum.token_prefix', ''),
            $tokenEntropy = Str::random(40),
            hash('crc32b', $tokenEntropy)
        );

        $token = $this->refreshTokens()->create([
            'access_token_id' => $accessTokenId,
            'token' => hash('sha256', $plainTextToken),
            'expires_at' => $expiresAt,
        ]);

        return new NewRefreshToken($token, $token->getKey().'|'.$plainTextToken);
    }

    /**
     * Set the current access token for the user.
     */
    public function withRefreshToken(?PersonalRefreshToken $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }
}
