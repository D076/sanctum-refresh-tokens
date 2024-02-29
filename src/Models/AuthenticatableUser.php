<?php

namespace D076\SanctumRefreshTokens\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use D076\SanctumRefreshTokens\HasApiTokensInterface;
use D076\SanctumRefreshTokens\Traits\HasApiTokens;

abstract class AuthenticatableUser extends Authenticatable implements HasApiTokensInterface
{
    use HasApiTokens, Notifiable;
}
