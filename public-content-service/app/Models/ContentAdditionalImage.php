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
        'caption',
        'order',
        'created_by',
        'updated_by',
    ];
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_type', 'content_id'];

    public function content()
    {
        return $this->morphTo();
    }
}
