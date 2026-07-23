<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailAccordionGroup extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function list()
    {
        return $this->hasMany(ContentDetailAccordionList::class)->orderBy('order', 'asc');
    }
}
