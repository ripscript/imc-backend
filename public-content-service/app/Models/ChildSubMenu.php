<?php

namespace App\Models;

use App\Traits\HasAdditionalContentMapping;
use App\Traits\HasContentMapping;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildSubMenu extends Model
{
    protected $guarded = ['id'];
    use HasAdditionalContentMapping, HasContentMapping, HasFactory;

    protected $hidden = ['id', 'sub_menu_id', 'created_at', 'updated_at', 'created_by', 'updated_by'];

    public function content()
    {
        return $this->morphOne(Content::class, 'parent');
    }

    public function subMenu()
    {
        return $this->belongsTo(SubMenu::class);
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
