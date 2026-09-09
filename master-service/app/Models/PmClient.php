<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmClient extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_clients';

    protected $guarded = ['id'];

    protected $logName = 'client';
}
