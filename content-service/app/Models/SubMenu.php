<?php

namespace App\Models;

use App\Services\GlobalSearchService;
use App\Traits\HasActivityLog;
use App\Traits\HasAdditionalContentMapping;
use App\Traits\HasContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubMenu extends Model
{
    use HasActivityLog, HasAdditionalContentMapping, HasContentMapping, HasFactory;

    protected $fillable = [
        'menu_id',
        'title',
        'description',
        'slug',
        'is_active',
        'order',
        'has_child',
        'is_show_side_menu',
        'created_by',
        'updated_by',
    ];

    protected $logName = 'sub menu';

    public function content()
    {
        return $this->morphOne(Content::class, 'parent');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }

    public function childSubMenu()
    {
        return $this->hasMany(ChildSubMenu::class, 'sub_menu_id', 'id');
    }

    protected static function booted()
    {
        static::deleted(function ($sub) {
            $sub->content?->delete();

            $service = new GlobalSearchService;
            $service->delete(SubMenu::class, $sub->id);
        });
    }

    public function getFullUrlAttribute()
    {
        return '/'.$this->menu?->slug.'/'.$this->slug;
    }
}
