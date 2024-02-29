<?php

namespace D076\SanctumRefreshTokens\Enums;

enum TokenType: string
{
    case ACCESS_TOKEN = 'access-token';
    case REFRESH_TOKEN = 'refresh-token';
}
