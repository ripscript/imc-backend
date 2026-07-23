<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LatestInformation extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'latest_informations';
}
