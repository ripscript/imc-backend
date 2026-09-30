<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmWorkflowStageDeliverable extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pm_workflow_stage_deliverables';

    protected $guarded = ['id'];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function workflowStage()
    {
        return $this->belongsTo(PmWorkflowStage::class, 'workflow_stage_id');
    }
}
