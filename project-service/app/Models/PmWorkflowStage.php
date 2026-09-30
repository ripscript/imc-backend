<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmWorkflowStage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pm_workflow_stages';

    protected $guarded = ['id'];

    protected $casts = [
        'sequence' => 'integer',
        'is_required' => 'boolean',
        'allow_skip' => 'boolean',
        'allow_repeat' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(PmWorkflow::class, 'workflow_id');
    }

    public function tasks()
    {
        return $this->hasMany(PmWorkflowStageTask::class, 'workflow_stage_id')->orderBy('sequence');
    }

    public function deliverables()
    {
        return $this->hasMany(PmWorkflowStageDeliverable::class, 'workflow_stage_id')->orderBy('sequence');
    }
}
