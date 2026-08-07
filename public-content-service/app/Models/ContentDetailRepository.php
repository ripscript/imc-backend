<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailRepository extends Model
{
    use HasFactory;
    protected $fillable = [
        'content_id',
        'title',
        'slug',
        'description',
        'type',
        'is_publicated',
        'layout_id',
        'publish_date',
        'thumbnail_path',
        'order',
        'created_by',
        'updated_by',
    ];
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_id', 'is_publicated', 'layout_id'];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function contentTag()
    {
        return $this->morphMany(ContentTag::class, 'content');
    }

    public function layout()
    {
        return $this->belongsTo(MsLayout::class);
    }
}
