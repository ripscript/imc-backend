<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmWorkflowStageTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pm_workflow_stage_tasks';

    protected $guarded = ['id'];

    public function workflowStage()
    {
        return $this->belongsTo(PmWorkflowStage::class, 'workflow_stage_id');
    }
}
