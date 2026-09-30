<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmChangeRequest extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_change_requests';

    protected $guarded = ['id'];

    protected $logName = 'Change Request';

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function stage()
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
