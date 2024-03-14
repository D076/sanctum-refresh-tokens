<?php

namespace D076\SanctumRefreshTokens\Enums;

enum TokenType: string
{
    case AccessToken = 'access-token';
    case RefreshToken = 'refresh-token';
}
