<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsPerwakilan extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'ms_perwakilan';

    public function permohonan()
    {
        return $this->morphOne(Permohonan::class, 'destination_institution');
    }
}
