<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmWorkflowStageDeliverable extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_workflow_stage_deliverables';

    protected $guarded = ['id'];

    protected $logName = 'workflow_stage_deliverable';

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function workflowStage()
    {
        return $this->belongsTo(PmWorkflowStage::class, 'workflow_stage_id');
    }

    public function documentType()
    {
        return $this->belongsTo(PmDocumentType::class, 'document_type_id');
    }
}
