<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsPerwakilanNegara extends Model
{
    use HasFactory;

    protected $table = 'ms_perwakilan_negara';

    protected $guarded = ['id'];
}
