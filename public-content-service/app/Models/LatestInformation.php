<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LatestInformation extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'url',
        'publish_date',
        'is_active',
        'created_by',
        'updated_by',
    ];
    protected $table = 'latest_informations';
}
