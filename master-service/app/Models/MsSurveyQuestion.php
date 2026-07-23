<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsSurveyQuestion extends Model
{
    use HasActivityLog, HasFactory;

    protected $guarded = ['id'];

    protected $table = 'ms_survey_questions';

    protected $logName = 'pertanyaan survei kepuasan';
}
