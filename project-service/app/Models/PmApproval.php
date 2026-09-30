<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmApproval extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_approvals';

    protected $guarded = ['id'];

    protected $logName = 'Approval';

    protected $casts = [
        'sequence' => 'integer',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function stage()
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
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
