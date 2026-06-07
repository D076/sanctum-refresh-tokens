<?php

namespace D076\SanctumRefreshTokens\Tests\Fixtures;

use D076\SanctumRefreshTokens\Models\AuthenticatableUser;

class User extends AuthenticatableUser
{
    protected $table = 'users';

    protected $guarded = [];
}
