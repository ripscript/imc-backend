<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmClient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pm_clients';

    protected $guarded = ['id'];
}
