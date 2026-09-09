<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmWorkflow extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_workflows';

    protected $guarded = ['id'];

    protected $logName = 'workflow';

    public function stages()
    {
        return $this->hasMany(PmWorkflowStage::class, 'workflow_id');
    }
}
