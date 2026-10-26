<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailGallery extends Model
{
    use HasFactory;
    protected $fillable = [
        'content_id',
        'title',
        'slug',
        'description',
        'type',
        'sub_type',
        'is_publicated',
        'layout_id',
        'publish_date',
        'order',
        'created_by',
        'updated_by',
    ];
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

    public function layout()
    {
        return $this->belongsTo(MsLayout::class);
    }
}
