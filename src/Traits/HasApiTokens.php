<?php

namespace D076\SanctumRefreshTokens\Traits;

use Laravel\Sanctum\HasApiTokens as SanctumTokens;

trait HasApiTokens
{
    use HasRefreshTokens;
    use SanctumTokens;
}
