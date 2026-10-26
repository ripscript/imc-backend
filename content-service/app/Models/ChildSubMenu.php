<?php

namespace App\Models;

use App\Services\GlobalSearchService;
use App\Traits\HasActivityLog;
use App\Traits\HasAdditionalContentMapping;
use App\Traits\HasContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildSubMenu extends Model
{
    use HasActivityLog, HasAdditionalContentMapping, HasContentMapping, HasFactory;

    protected $fillable = [
        'sub_menu_id',
        'title',
        'slug',
        'is_active',
        'order',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['menu_id'];

    protected $logName = 'child sub menu';

    public function content()
    {
        return $this->morphOne(Content::class, 'parent');
    }

    public function subMenu()
    {
        return $this->belongsTo(SubMenu::class, 'sub_menu_id', 'id');
    }

    protected static function booted()
    {
        static::deleted(function ($child) {
            $child->content?->delete();

            $service = new GlobalSearchService;
            $service->delete(ChildSubMenu::class, $child->id);
        });
    }

    public function getMenuIdAttribute()
    {
        return $this->subMenu?->menu_id;
    }

    public function getFullUrlAttribute()
    {
        return '/'.$this->subMenu?->menu?->slug.'/'.$this->subMenu?->slug.'/'.$this->slug;
    }
}
