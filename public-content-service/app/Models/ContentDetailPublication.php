<?php

namespace App\Models;

use App\Traits\HasAdditionalContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailPublication extends Model
{
    use HasFactory, HasAdditionalContentMapping;
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_id', 'is_publicated', 'layout_id', 'kategori_content_id', 'kategori'];
    protected $appends = ['kategori_title'];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function kategori()
    {
        return $this->belongsTo(MsKategoriContent::class, 'kategori_content_id', 'id');
    }

    public function contentAdditionalImage()
    {
        return $this->morphMany(ContentAdditionalImage::class, 'content')->orderBy('order', 'asc');
    }

    public function contentAdditionalVideo()
    {
        return $this->morphMany(ContentAdditionalVideo::class, 'content')->orderBy('order', 'asc');
    }

    public function contentAdditionalLink()
    {
        return $this->morphMany(ContentAdditionalLink::class, 'content')->orderBy('order', 'asc');
    }

    public function contentAdditionalFile()
    {
        return $this->morphMany(ContentAdditionalFile::class, 'content')->orderBy('order', 'asc');
    }

    public function contentAdditionalText()
    {
        return $this->morphOne(ContentAdditionalText::class, 'content');
    }

    public function contentTag()
    {
        return $this->morphMany(ContentTag::class, 'content');
    }

    function layout()
    {
        return $this->belongsTo(MsLayout::class);
    }

    public function getKategoriTitleAttribute()
    {
        return $this->kategori?->title;
    }
}
