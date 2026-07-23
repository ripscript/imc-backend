<?php

namespace App\Models;

use App\Services\GlobalSearchService;
use App\Traits\HasActivityLog;
use App\Traits\HasAdditionalContentMapping;
use App\Traits\HasContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasActivityLog, HasAdditionalContentMapping, HasContentMapping, HasFactory;

    protected $guarded = ['id'];

    protected $logName = 'menu';

    public function content()
    {
        return $this->morphOne(Content::class, 'parent');
    }

    public function subMenu()
    {
        return $this->hasMany(SubMenu::class, 'menu_id', 'id');
    }

    public function childSubMenuThrough()
    {
        return $this->hasManyThrough(ChildSubMenu::class, SubMenu::class, 'menu_id', 'sub_menu_id', 'id', 'id');
    }

    public function countMenusWithShowFront()
    {
        return self::where('is_show_front', true)
            ->where('slug', '!=', 'beranda')
            ->where('id', '!=', $this->id)
            ->count();
    }

    protected static function booted()
    {
        static::deleted(function ($menu) {
            $menu->content?->delete();

            $service = new GlobalSearchService;
            $service->delete(Menu::class, $menu->id);
        });
    }

    public function getFullUrlAttribute()
    {
        return '/'.$this->slug;
    }
}
