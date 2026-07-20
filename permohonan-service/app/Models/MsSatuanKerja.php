<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsSatuanKerja extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'ms_satuan_kerja';

    public function permohonan()
    {
        return $this->morphOne(Permohonan::class, 'destination_institution');
    }
}
