<?php

namespace D076\SanctumRefreshTokens;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;

/**
 * @implements Arrayable<string, mixed>
 */
class NewRefreshToken implements Arrayable, Jsonable
{
    public PersonalRefreshToken $refreshToken;

    /**
     * The plain text version of the token.
     */
    public string $plainTextToken;

    /**
     * Create a new refresh token result.
     */
    public function __construct(PersonalRefreshToken $refreshToken, string $plainTextToken)
    {
        $this->refreshToken = $refreshToken;
        $this->plainTextToken = $plainTextToken;
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'refreshToken' => $this->refreshToken,
            'plainTextToken' => $this->plainTextToken,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
