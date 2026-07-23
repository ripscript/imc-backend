<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsKategoriFaq extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'ms_kategori_faq';

    protected $fillable = ['name', 'created_by', 'updated_by'];

    protected $logName = 'kategori FAQ';
}
