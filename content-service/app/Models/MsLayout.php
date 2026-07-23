<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsLayout extends Model
{
    use HasFactory;

    protected $table = 'ms_layout';

    protected $fillable = ['name', 'is_active', 'created_by', 'updated_by'];

    public static function findByType($type)
    {
        return static::where('type', $type)->firstOrFail();
    }
}
