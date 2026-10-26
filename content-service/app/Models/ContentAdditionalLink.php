<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentAdditionalLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_type',
        'content_id',
        'title',
        'link',
        'order',
        'created_by',
        'updated_by',
    ];

    public function content()
    {
        return $this->morphTo();
    }
}
