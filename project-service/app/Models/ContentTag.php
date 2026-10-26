<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_type',
        'content_id',
        'tag_id',
        'created_by',
        'updated_by',
    ];

    public function tag()
    {
        return $this->belongsTo(MsTag::class, 'tag_id', 'id');
    }

    public function content()
    {
        return $this->morphTo();
    }
}
