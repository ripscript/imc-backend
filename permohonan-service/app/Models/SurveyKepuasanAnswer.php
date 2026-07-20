<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyKepuasanAnswer extends Model
{
    use HasFactory;
    protected $table = 'survey_kepuasan_answers';
    protected $guarded = ['id'];

    public function question()
    {
        return $this->belongsTo(MsSurveyQuestion::class, 'survey_question_id');
    }

    public function skalaLikert()
    {
        return $this->belongsTo(MsSurveySkalaLikert::class, 'answer');
    }

    public function getReadableAnswerAttribute()
    {
        return $this->question?->type == 'skala_likert' ? $this->skalaLikert?->label : $this->answer;
    }

}
