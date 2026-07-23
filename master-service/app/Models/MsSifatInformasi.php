<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsSifatInformasi extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'ms_sifat_informasi';

    protected $fillable = ['name', 'is_active', 'created_by', 'updated_by'];

    protected $logName = 'sifat informasi';
}
