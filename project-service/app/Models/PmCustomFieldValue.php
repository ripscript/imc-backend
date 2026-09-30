<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmCustomFieldValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pm_custom_field_values';

    protected $guarded = ['id'];

    public function field()
    {
        return $this->belongsTo(PmCustomField::class, 'custom_field_id');
    }
}
