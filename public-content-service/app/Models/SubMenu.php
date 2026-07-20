<?php

namespace App\Models;

use App\Traits\HasAdditionalContentMapping;
use App\Traits\HasContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubMenu extends Model
{
    use HasFactory, HasContentMapping, HasAdditionalContentMapping;
    protected $hidden = ['id', 'menu_id', 'created_at', 'updated_at', 'created_by', 'updated_by'];

    public function content()
    {
        return $this->morphOne(Content::class, 'parent');
    }

    public function childSubMenu()
    {
        return $this->hasMany(ChildSubMenu::class, 'sub_menu_id')
            ->active()
            ->orderBy('order', 'asc');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function findBySlug($slug)
    {
        return static::where('slug', $slug)->first();
    }
}
