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
        'order',
        'created_by',
        'updated_by',
    ];

    public function content()
    {
        return $this->morphTo();
    }
}
