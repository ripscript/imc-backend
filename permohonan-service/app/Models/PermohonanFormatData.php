<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanFormatData extends Model
{
    use HasFactory;
    protected $table = 'permohonan_format_data';
    protected $guarded = ['id'];

    public function master()
    {
        return $this->belongsTo(MsPermohonanFormatData::class, 'format_data_id');
    }
}
