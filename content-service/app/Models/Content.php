<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasActivityLog, HasFactory;

    protected $fillable = [
        'parent_type',
        'parent_id',
        'type',
        'layout_id',
        'section_title',
        'section_description',
        'is_section',
        'is_show_home',
        'is_show_image',
        'is_show_tag',
        'sub_type',
        'is_accessible',
        'order',
        'has_additional_image',
        'has_additional_video',
        'has_additional_file',
        'has_additional_link',
        'has_additional_text',
        'created_by',
        'updated_by',
    ];

    protected $logName = 'content';

    public function parent()
    {
        return $this->morphTo();
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

    public function contentDetailOrganizationalStructureGroup()
    {
        return $this->hasMany(ContentDetailOrganizationalStructureGroup::class)->orderBy('order', 'asc');
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
        return $this->morphMany(ContentAdditionalText::class, 'content')->orderBy('order', 'asc');
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
                return $this->resolveHeroCarouselSlides();
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
            case 'organizational_structure':
                return $this->contentDetailOrganizationalStructureGroup()->with('members')->get();
                break;
            case 'consume':
                return $this->contentDetailConsume;
                break;
        }
    }

    public function contentDetailShowcase()
    {
        return $this->hasMany(ContentDetailShowcase::class)->orderBy('order', 'asc');
    }

    /**
     * Resolve hero carousel slides. Each slide references an existing content
     * item (source_type + source_id). Source fields (image_path, title,
     * subtitle, tag, button_link) are resolved from the source at read time.
     * button_text is the only inline override. Source items are batch-loaded
     * per type with contentTag eager-loaded to avoid N+1.
     */
    private function resolveHeroCarouselSlides()
    {
        $slides = $this->contentDetailHeroCarousel;

        $idsByType = [];
        foreach ($slides as $slide) {
            if ($slide->source_type && $slide->source_id) {
                $idsByType[$slide->source_type][] = $slide->source_id;
            }
        }

        $sources = [
            'publication' => collect(),
            'repository' => collect(),
            'gallery' => collect(),
        ];

        foreach ($idsByType as $type => $ids) {
            $uniqueIds = array_values(array_unique($ids));
            if (empty($uniqueIds)) {
                continue;
            }

            switch ($type) {
                case 'publication':
                    $rows = ContentDetailPublication::whereIn('id', $uniqueIds)
                        ->with('contentTag.tag')
                        ->get(['id', 'title', 'slug', 'headline as subtitle', 'thumbnail_path as image_path']);
                    break;
                case 'repository':
                    $rows = ContentDetailRepository::whereIn('id', $uniqueIds)
                        ->with('contentTag.tag')
                        ->get(['id', 'title', 'slug', 'description as subtitle', 'thumbnail_path as image_path']);
                    break;
                case 'gallery':
                    $rows = ContentDetailGallery::whereIn('id', $uniqueIds)
                        ->with(['contentTag.tag', 'list'])
                        ->get(['id', 'title', 'slug', 'description as subtitle']);
                    foreach ($rows as $row) {
                        $row->image_path = $row->list->sortBy('order')->first()?->image_path;
                    }
                    break;
                default:
                    $rows = collect();
            }

            $sources[$type] = $rows->keyBy('id');
        }

        $prefixes = [
            'publication' => '/berita/',
            'repository' => '/repository/',
            'gallery' => '/galeri/',
        ];

        return $slides->map(function ($slide) use ($sources, $prefixes) {
            $data = [
                'id' => $slide->id,
                'image_path' => null,
                'tag' => null,
                'title' => null,
                'subtitle' => null,
                'button_text' => $slide->button_text,
                'button_link' => null,
                'order' => $slide->order,
                'source_type' => $slide->source_type,
                'source_id' => $slide->source_id,
            ];

            if ($slide->source_type && $slide->source_id && isset($sources[$slide->source_type])) {
                $source = $sources[$slide->source_type]->get($slide->source_id);
                if ($source) {
                    $data['image_path'] = $source->image_path;
                    $data['title'] = $source->title;
                    $data['subtitle'] = $source->subtitle;
                    $data['tag'] = $source->contentTag->first()?->tag_title;
                    $data['button_link'] = ($prefixes[$slide->source_type] ?? '/').$source->slug;
                }
            }

            return $data;
        })->values();
    }

    protected static function booted()
    {
        static::deleting(function ($content) {
            $content->contentAdditionalImage()->delete();
            $content->contentAdditionalVideo()->delete();
            $content->contentAdditionalFile()->delete();
            $content->contentAdditionalLink()->delete();
            $content->contentAdditionalText()->delete();
        });
    }
}
