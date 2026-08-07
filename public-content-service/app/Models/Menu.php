<?php

namespace App\Models;

use App\Traits\HasAdditionalContentMapping;
use App\Traits\HasContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'order',
        'has_sub',
        'is_show_front',
        'is_active',
        'is_dedicated',
        'created_by',
        'updated_by',
    ];
    use HasAdditionalContentMapping, HasContentMapping, HasFactory;

    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'is_active', 'is_dedicated'];

    public function content()
    {
        return $this->morphOne(Content::class, 'parent');
    }

    public function subMenu()
    {
        return $this->hasMany(SubMenu::class, 'menu_id')
            ->active()
            ->orderBy('order', 'asc');
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
