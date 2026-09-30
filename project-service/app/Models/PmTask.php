<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmTask extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_tasks';

    protected $guarded = ['id'];

    protected $logName = 'Task';

    protected $casts = [
        'due_date' => 'date:Y-m-d',
    ];

    public function stage()
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
