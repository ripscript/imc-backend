<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LatestInformation extends Model
{
    use HasActivityLog, HasFactory;

    protected $guarded = ['id'];

    protected $table = 'latest_informations';

    protected $logName = 'informasi terkini';
}
