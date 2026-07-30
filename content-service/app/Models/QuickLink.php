<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickLink extends Model
{
    use HasActivityLog, HasFactory;

    protected $fillable = [
        'title',
        'url',
        'order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $table = 'quick_links';

    protected $logName = 'link cepat';
}
