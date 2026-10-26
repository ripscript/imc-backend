<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsKategoriContent extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'title',
        'is_active',
        'order',
        'created_by',
        'updated_by',
    ];
    protected $table = 'ms_kategori_contents';

    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'is_active'];
}
