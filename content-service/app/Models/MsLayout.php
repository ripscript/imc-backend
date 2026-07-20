<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsLayout extends Model
{
    use HasFactory;
    protected $table = 'ms_layout';

    public static function findByType($type)
    {
        return static::where('type', $type)->firstOrFail();
    }
}
