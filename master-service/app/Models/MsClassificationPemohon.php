<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsClassificationPemohon extends Model
{
    use HasFactory;

    protected $table = 'ms_classification_pemohon';
    protected $fillable = ['name', 'description', 'is_active'];
}
