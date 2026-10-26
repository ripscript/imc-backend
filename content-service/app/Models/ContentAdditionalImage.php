<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentAdditionalImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_type',
        'content_id',
        'image_path',
        'description',
        'order',
        'created_by',
        'updated_by',
    ];

    public function content()
    {
        return $this->morphTo();
    }
}
