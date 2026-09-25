<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'tblPermissions';

    protected $primaryKey = 'permission_id';

    public $timestamps = false;

    protected $fillable = [
        'permission_name',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'tblRolePermissions',
            'permission_id',
            'role_id',
            'permission_id',
            'role_id'
        );
    }
}