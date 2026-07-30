<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LatestInformation extends Model
{
    use HasActivityLog, HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'date',
        'url',
        'is_active',
        'order',
        'created_by',
        'updated_by',
    ];

    protected $table = 'latest_informations';

    protected $logName = 'informasi terkini';
}
