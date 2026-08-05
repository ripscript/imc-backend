<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsFaq extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'description',
        'kategori_faq_id',
        'is_active',
        'created_by',
        'updated_by',
    ];
    use HasFactory, SoftDeletes;

    protected $table = 'ms_faq';
}
