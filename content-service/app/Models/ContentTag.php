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

    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_type', 'content_id', 'tag_id', 'tag'];

    protected $appends = ['tag_title'];

    public function tag()
    {
        return $this->belongsTo(MsTag::class, 'tag_id', 'id');
    }

    public function content()
    {
        return $this->morphTo();
    }

    public function getTagTitleAttribute()
    {
        return $this->tag?->title;
    }
}
