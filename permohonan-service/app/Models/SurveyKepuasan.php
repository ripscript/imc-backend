<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyKepuasan extends Model
{
    use HasFactory;
    protected $table = 'survey_kepuasan';
    protected $guarded = ['id'];

    public function answers()
    {
        return $this->hasMany(SurveyKepuasanAnswer::class, 'survey_kepuasan_id');
    }

    public function pemohon()
    {
        return $this->belongsTo(Pemohon::class, 'pemohon_id');
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

}
