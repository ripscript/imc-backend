<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmDeliverable extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_deliverables';

    protected $guarded = ['id'];

    protected $logName = 'Deliverable';

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'is_required' => 'boolean',
    ];

    public function stage()
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function documentType()
    {
        return $this->belongsTo(PmDocumentType::class, 'document_type_id');
    }
}
