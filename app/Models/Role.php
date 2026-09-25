<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'tblRoles';

    protected $primaryKey = 'role_id';

    public $timestamps = false;

    protected $fillable = [
        'role_name',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    public function permissions(): BelongsToMany
    {
    return $this->belongsToMany(
        Permission::class,
        'tblRolePermissions',
        'role_id',
        'permission_id',
        'role_id',
        'permission_id'
    );
    }
}