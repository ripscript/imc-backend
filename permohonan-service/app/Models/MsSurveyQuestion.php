<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsSurveyQuestion extends Model
{
    use HasFactory;
    protected $table = 'ms_survey_questions';

    public static function findByUuid($uuid)
    {
        return static::where('uuid', $uuid)->first();
    }
}
