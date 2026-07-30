<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentAdditionalFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_type',
        'content_id',
        'title',
        'file_url',
        'order',
        'created_by',
        'updated_by',
    ];

    public function content()
    {
        return $this->morphTo();
    }
}
