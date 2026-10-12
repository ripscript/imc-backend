<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmStageApprover extends Model
{
    use HasActivityLog, HasFactory;

    protected $table = 'pm_stage_approvers';

    protected $guarded = ['id'];

    protected $logName = 'Stage Approver';

    public function stage()
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function projectStage()
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
