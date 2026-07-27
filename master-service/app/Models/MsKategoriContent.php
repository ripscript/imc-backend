<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsKategoriContent extends Model
{
    use HasActivityLog, HasFactory;

    protected $table = 'ms_kategori_contents';

    protected $fillable = ['title', 'is_active', 'created_by', 'updated_by'];

    protected $logName = 'kategori konten';
}
