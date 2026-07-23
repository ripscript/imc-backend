<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasActivityLog, HasFactory;

    protected $guarded = ['id'];

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
        }
    }

    protected static function booted()
    {
        static::deleting(function ($content) {
            $content->contentAdditionalImage()->delete();
            $content->contentAdditionalVideo()->delete();
            $content->contentAdditionalFile()->delete();
            $content->contentAdditionalLink()->delete();
            $content->contentAdditionalText?->delete();
        });
    }
}
