<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmWorkflow extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pm_workflows';

    protected $guarded = ['id'];

    public function stages()
    {
        return $this->hasMany(PmWorkflowStage::class, 'workflow_id')->orderBy('sequence');
    }
}
