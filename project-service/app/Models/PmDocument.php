<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmDocument extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_documents';

    protected $guarded = ['id'];

    protected $logName = 'Document';

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size_kb' => 'integer',
        'version' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function stage()
    {
        return $this->belongsTo(ProjectStage::class, 'project_stage_id');
    }

    public function documentType()
    {
        return $this->belongsTo(PmDocumentType::class, 'document_type_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
