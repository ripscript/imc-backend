<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects';

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'target_end_date' => 'date:Y-m-d',
        'progress' => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(PmClient::class, 'client_id');
    }

    public function partner()
    {
        return $this->belongsTo(PmPartner::class, 'partner_id');
    }

    public function category()
    {
        return $this->belongsTo(PmProjectCategory::class, 'category_id');
    }

    public function workflow()
    {
        return $this->belongsTo(PmWorkflow::class, 'workflow_id');
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function attachments()
    {
        return $this->hasMany(ProjectAttachment::class, 'project_id');
    }
}
