<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsPermohonanAksesData extends Model
{
    use HasFactory;

    protected $table = 'ms_permohonan_akses_data';
    protected $fillable = ['name'];
}
