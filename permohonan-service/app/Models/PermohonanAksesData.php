<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanAksesData extends Model
{
    use HasFactory;
    protected $table = 'permohonan_akses_data';
    protected $guarded = ['id'];

    public function master()
    {
        return $this->belongsTo(MsPermohonanAksesData::class, 'akses_data_id');
    }
}
