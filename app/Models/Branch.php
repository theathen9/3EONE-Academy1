<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'tblBranches';

    protected $primaryKey = 'branch_id';

    public $timestamps = false;

    protected $fillable = [
        'branch_code',
        'branch_name',
        'phone1',
        'phone2',
        'email',
        'address',
        'location',
        'status',
        'created_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
    ];
}
