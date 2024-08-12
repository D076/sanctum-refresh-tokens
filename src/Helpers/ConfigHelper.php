<?php

namespace D076\SanctumRefreshTokens\Helpers;

use Illuminate\Support\Carbon;

class ConfigHelper
{
    public static function getAccessTokenExpiresAt(): Carbon
    {
        return now()->addMinutes(config('sanctum.expiration', 60));
    }

    public static function getRefreshTokenExpiresAt(bool $remember = false): Carbon
    {
        return $remember
            ? now()->addMinutes(config('sanctum.refresh_token_expiration', 43200))
            : now()->addMinutes(config('sanctum.refresh_token_expiration_no_remember', 1440));
    }
}