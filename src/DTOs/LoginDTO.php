<?php

namespace D076\SanctumRefreshTokens\DTOs;

use Illuminate\Contracts\Support\Arrayable;

final readonly class LoginDTO implements Arrayable
{
    public function __construct(
        public string $email,
        public string $password,
        public string $model,
        public bool $remember = false,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
