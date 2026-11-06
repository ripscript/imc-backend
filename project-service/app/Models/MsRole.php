<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsRole extends Model
{
    use HasFactory;

    protected $table = 'ms_roles';

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'is_system',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function permissions()
    {
        return $this->belongsToMany(MsPermission::class, 'ms_role_permissions', 'role_id', 'permission_id')
            ->withTimestamps();
    }
}
