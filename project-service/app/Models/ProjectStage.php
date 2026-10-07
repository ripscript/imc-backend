<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectStage extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_project_stages';

    protected $guarded = ['id'];

    protected $logName = 'Project Stage';

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'progress' => 'integer',
        'is_required' => 'boolean',
        'allow_skip' => 'boolean',
        'allow_repeat' => 'boolean',
        'requires_approval' => 'boolean',
        'is_configured' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function workflowStage()
    {
        return $this->belongsTo(PmWorkflowStage::class, 'workflow_stage_id');
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function tasks()
    {
        return $this->hasMany(PmTask::class, 'project_stage_id');
    }

    public function deliverables()
    {
        return $this->hasMany(PmDeliverable::class, 'project_stage_id');
    }

    public function documents()
    {
        return $this->hasMany(PmDocument::class, 'project_stage_id');
    }

    /**
     * Admin that cleared this stage (exit-gate approval, specs/005).
     * Set only when `requires_approval` is true.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dependsOn()
    {
        return $this->belongsTo(self::class, 'depends_on_project_stage_id');
    }

    public function dependents()
    {
        return $this->hasMany(self::class, 'depends_on_project_stage_id');
    }

    /**
     * Derived stage progress: 100 when completed/skipped, otherwise the
     * share of done tasks (0 when the stage has no tasks yet).
     */
    public function calculateProgress(): int
    {
        if (in_array($this->status, ['completed', 'skipped'], true)) {
            return 100;
        }

        $tasks = $this->relationLoaded('tasks') ? $this->tasks : $this->tasks()->get();

        if ($tasks->isEmpty()) {
            return 0;
        }

        $done = $tasks->where('status', 'done')->count();

        return (int) round($done / $tasks->count() * 100);
    }
}
