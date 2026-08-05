<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentAdditionalVideo extends Model
{
    use HasFactory;
    protected $fillable = [
        'content_type',
        'content_id',
        'video_url',
        'thumbnail_path',
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
