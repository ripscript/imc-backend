<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentDetailAccordionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'title',
        'order',
        'created_by',
        'updated_by',
    ];

    public function list()
    {
        return $this->hasMany(ContentDetailAccordionList::class)->orderBy('order', 'asc');
    }
}
