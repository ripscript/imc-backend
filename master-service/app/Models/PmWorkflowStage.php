<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmWorkflowStage extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_workflow_stages';

    protected $guarded = ['id'];

    protected $logName = 'workflow_stage';

    public function workflow()
    {
        return $this->belongsTo(PmWorkflow::class, 'workflow_id');
    }
}
