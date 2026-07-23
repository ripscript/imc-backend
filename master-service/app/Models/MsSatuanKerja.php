<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsSatuanKerja extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'ms_satuan_kerja';

    protected $guarded = ['id'];

    protected $logName = 'satuan kerja';

    public function subSatuanKerja()
    {
        return $this->hasMany(MsSubSatuanKerja::class, 'satuan_kerja_id', 'id');
    }
}
