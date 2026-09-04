<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;
    protected $fillable = [
        'layout_id',
        'parent_type',
        'parent_id',
        'type',
        'sub_type',
        'title',
        'slug',
        'section_title',
        'order',
        'is_section',
        'is_accessible',
        'created_by',
        'updated_by',
    ];
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'layout_id', 'parent_type', 'parent_id'];

    public function parent()
    {
        return $this->morphTo();
    }

    public function layout()
    {
        return $this->belongsTo(MsLayout::class);
    }

    public function contentDetailImageLink()
    {
        return $this->hasMany(ContentDetailImageLink::class)->orderBy('order', 'asc');
    }

    public function contentDetailImageCarousel()
    {
        return $this->hasMany(ContentDetailImageCarousel::class)->orderBy('order', 'asc');
    }

    public function contentDetailHeroCarousel()
    {
        return $this->hasMany(ContentDetailHeroCarousel::class)->orderBy('order', 'asc');
    }

    public function contentDetailCardLink()
    {
        return $this->hasMany(ContentDetailCardLink::class)->orderBy('order', 'asc');
    }

    public function contentDetailVideoCarousel()
    {
        return $this->hasMany(ContentDetailVideoCarousel::class)->orderBy('order', 'asc');
    }

    public function contentDetailAccordionGroup()
    {
        return $this->hasMany(ContentDetailAccordionGroup::class)->orderBy('order', 'asc');
    }

    public function contentDetailText()
    {
        return $this->hasOne(ContentDetailText::class);
    }

    public function contentDetailConsume()
    {
        return $this->hasMany(ContentDetailConsume::class)->orderBy('order', 'asc');
    }

    public function contentDetailAgenda()
    {
        return $this->hasMany(ContentDetailAgenda::class)->where('is_active', true)->orderBy('datetime_start', 'desc');
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

    public function getDetailBasedOnType()
    {
        switch ($this->type) {
            case 'card_link':
                return $this->contentDetailCardLink;
                break;
            case 'image_carousel':
                return $this->contentDetailImageCarousel;
                break;
            case 'hero_carousel':
                return $this->contentDetailHeroCarousel;
                break;
            case 'image_link':
                return $this->contentDetailImageLink;
                break;
            case 'text':
                return $this->contentDetailText;
                break;
            case 'video_carousel':
                return $this->contentDetailVideoCarousel;
                break;
            case 'accordion':
                return $this->contentDetailAccordionGroup()->with('list')->get();
                break;
            case 'consume':
                return $this->contentDetailConsume;
                break;
            case 'agenda':
                return $this->contentDetailAgenda;
                break;
        }
    }

    public function scopeAccessible($query)
    {
        return $query->where('is_accessible', true);
    }

    public function getParentSlugAttribute()
    {
        $parent = $this->parent;

        if (! $parent) {
            return [
                'type' => null,
                'full_path' => null,
                'current_path' => null,
                'title' => null,
            ];
        }

        $parentClass = get_class($parent);

        switch ($parentClass) {
            case Menu::class:
                $slug = $parent->slug;
                $type = 'menu';
                break;
            case SubMenu::class:
                $menuSlug = $parent->menu->slug;
                $slug = $menuSlug.'/'.$parent->slug;
                $type = 'sub_menu';
                break;
            case ChildSubMenu::class:
                $menuSlug = $parent->subMenu->menu->slug;
                $subMenuSlug = $parent->subMenu->slug;
                $slug = $menuSlug.'/'.$subMenuSlug.'/'.$parent->slug;
                $type = 'child_sub_menu';
                break;
            default:
                $slug = '';
                break;
        }

        return [
            'type' => $type,
            'full_path' => $slug,
            'current_path' => $parent->slug,
            'title' => $parent->title,
        ];
    }
}
