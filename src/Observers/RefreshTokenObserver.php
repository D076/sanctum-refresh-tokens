<?php

namespace D076\SanctumRefreshTokens\Observers;

use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;

class RefreshTokenObserver
{
    public function deleting(PersonalRefreshToken $refreshToken): void
    {
        if ($refreshToken->accessToken) {
            $refreshToken->accessToken->delete();
        }
    }
}
