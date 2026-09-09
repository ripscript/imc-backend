<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmProjectCategory extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_project_categories';

    protected $guarded = ['id'];

    protected $logName = 'project_category';
}
