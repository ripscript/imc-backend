<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsPermission extends Model
{
    use HasFactory;

    protected $table = 'ms_permissions';

    protected $fillable = [
        'name',
        'action',
        'code',
        'label',
        'group',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(MsRole::class, 'ms_role_permissions', 'permission_id', 'role_id')
            ->withTimestamps();
    }
}
