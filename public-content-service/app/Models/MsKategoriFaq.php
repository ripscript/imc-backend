<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsKategoriFaq extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'ms_kategori_faq';

    public function faqActive()
    {
        return $this->hasMany(MsFaq::class, 'kategori_faq_id')->where('is_active', true);
    }
}
