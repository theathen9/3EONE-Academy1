<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserToken extends Model
{
    protected $table = 'tblUserTokens';

    protected $primaryKey = 'token_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'device_id',
        'jti',
        'access_expiry',
        'refresh_token',
        'refresh_expiry',
        'device_info',
        'user_agent',
        'ip_address',
        'revoked_at',
    ];

    protected $hidden = [
        'refresh_token',
    ];

    protected $casts = [
        'access_expiry' => 'datetime',
        'refresh_expiry' => 'datetime',
        'created_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * User who owns this token session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'user_id'
        );
    }
}
