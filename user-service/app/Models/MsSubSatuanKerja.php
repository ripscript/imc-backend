<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsSubSatuanKerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ms_sub_satuan_kerja';

    protected $guarded = ['id'];
}
