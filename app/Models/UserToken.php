<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $table = 'tblUserTokens';

    protected $primaryKey = 'token_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'access_expiry',
        'refresh_expiry',
        'user_agent',
        'ip_address',
    ];

    protected $casts = [
        'access_expiry' => 'datetime',
        'refresh_expiry' => 'datetime',
    ];
}