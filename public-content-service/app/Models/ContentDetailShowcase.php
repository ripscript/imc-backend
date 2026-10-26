<?php

namespace App\Models;

use App\Traits\HasAdditionalContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailShowcase extends Model
{
    use HasAdditionalContentMapping, HasFactory;

    protected $fillable = [
        'content_id',
        'title',
        'slug',
        'description',
        'thumbnail_path',
        'is_publicated',
        'publish_date',
        'layout_id',
        'has_additional_image',
        'has_additional_video',
        'has_additional_file',
        'has_additional_link',
        'has_additional_text',
        'duration',
        'location',
        'start_date',
        'budget',
        'organization_address',
        'organization_name',
        'kategori_content_id',
        'created_by',
        'updated_by',
    ];

    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'];

    protected $appends = ['kategori_title'];

    public function kategoriContent()
    {
        return $this->belongsTo(MsKategoriContent::class, 'kategori_content_id');
    }

    public function layout()
    {
        return $this->belongsTo(MsLayout::class, 'layout_id');
    }

    public function getKategoriTitleAttribute()
    {
        return $this->kategoriContent?->title;
    }
}
