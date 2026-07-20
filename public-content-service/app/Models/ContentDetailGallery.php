<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailGallery extends Model
{
    use HasFactory;
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_id', 'is_publicated', 'layout_id'];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function list()
    {
        return $this->hasMany(ContentDetailGalleryList::class)->orderBy('order', 'asc');
    }

    public function contentTag()
    {
        return $this->morphMany(ContentTag::class, 'content');
    }

    function layout()
    {
        return $this->belongsTo(MsLayout::class);
    }
}
