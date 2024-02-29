<?php

namespace D076\SanctumRefreshTokens\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property-read int $id
 * @property string $token
 * @property int|null $access_token_id
 * @property Carbon|null $expires_at
 * @property-read AuthenticatableUser|null $tokenable
 * @property-read PersonalAccessToken|null $accessToken
 */
class PersonalRefreshToken extends Model
{
    protected $table = 'personal_refresh_tokens';

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $fillable = [
        'access_token_id',
        'token',
        'expires_at',
    ];

    protected $hidden = [
        'access_token_id',
        'token',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo('tokenable');
    }

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'access_token_id', 'id');
    }

    /**
     * Find the token instance matching the given token.
     *
     * @return static|null
     */
    public static function findToken(string $token): ?PersonalRefreshToken
    {
        $now = now();

        if (! str_contains($token, '|')) {
            return static::where('expires_at', '>', $now)->where('token', hash('sha256', $token))->first();
        }

        [$id, $token] = explode('|', $token, 2);

        if ($instance = static::query()->where('expires_at', '>', $now)->find($id)) {
            return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
        }

        return null;
    }
}
