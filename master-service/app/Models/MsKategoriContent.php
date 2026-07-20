<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsKategoriContent extends Model
{
    use HasFactory, HasActivityLog;
    protected $table = 'ms_kategori_contents';
    protected $logName = 'kategori konten';
}
