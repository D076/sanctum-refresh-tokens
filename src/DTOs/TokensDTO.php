<?php

namespace D076\SanctumRefreshTokens\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use D076\SanctumRefreshTokens\Models\AuthenticatableUser;

final readonly class TokensDTO implements Arrayable
{
    public function __construct(
        public string $model,
        public string $token_type,
        public string $access_token,
        public string $refresh_token,
        public ?int $access_token_expires_at,
        public ?int $refresh_token_expires_at,
        public AuthenticatableUser $user,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
