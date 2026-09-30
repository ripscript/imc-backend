<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmDocumentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pm_document_types';

    protected $guarded = ['id'];

    protected $casts = [
        'max_size_mb' => 'integer',
    ];
}
