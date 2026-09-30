<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmMilestone extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_milestones';

    protected $guarded = ['id'];

    protected $logName = 'Milestone';

    protected $casts = [
        'target_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
