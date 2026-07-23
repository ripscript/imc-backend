<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentAdditionalFile extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_type', 'content_id'];

    public function content()
    {
        return $this->morphTo();
    }
}
