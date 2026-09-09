<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmDocumentType extends Model
{
    use HasActivityLog, HasFactory, SoftDeletes;

    protected $table = 'pm_document_types';

    protected $guarded = ['id'];

    protected $logName = 'document_type';
}
