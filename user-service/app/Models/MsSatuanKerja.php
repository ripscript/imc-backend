<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsSatuanKerja extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'ms_satuan_kerja';
    protected $guarded = ['id'];

    public function subSatuanKerja()
    {
        return $this->hasMany(MsSubSatuanKerja::class, 'satuan_kerja_id', 'id');
    }
}
