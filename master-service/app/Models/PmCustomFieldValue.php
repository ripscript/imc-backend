<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmCustomFieldValue extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_custom_field_values';

    protected $guarded = ['id'];

    protected $logName = 'custom_field_value';

    public function customField()
    {
        return $this->belongsTo(PmCustomField::class, 'custom_field_id');
    }
}
