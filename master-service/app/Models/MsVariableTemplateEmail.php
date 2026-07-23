<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsVariableTemplateEmail extends Model
{
    use HasFactory;

    protected $table = 'ms_variable_template_email';

    protected $fillable = ['name', 'is_active', 'created_by', 'updated_by'];
}
