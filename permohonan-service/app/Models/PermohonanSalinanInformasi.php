<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanSalinanInformasi extends Model
{
    use HasFactory;
    protected $table = 'permohonan_salinan_informasi';
    protected $guarded = ['id'];

    public function master()
    {
        return $this->belongsTo(MsPermohonanSalinanInformasi::class, 'salinan_informasi_id');
    }
}
