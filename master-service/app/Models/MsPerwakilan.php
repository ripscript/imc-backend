<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MsPerwakilan extends Model
{
    use HasFactory, SoftDeletes, HasActivityLog;
    protected $table = 'ms_perwakilan';
    protected $guarded = ['id'];
    protected $logName = 'perwakilan';

    public function negara()
    {
        return $this->belongsToMany(MsNegara::class, 'ms_perwakilan_negara', 'perwakilan_id', 'negara_id')
            ->whereNull('ms_negara.deleted_at');
    }

    protected static function booted()
    {
        static::creating(function ($perwakilan) {
            $perwakilan->full_name = $perwakilan->type ." ". $perwakilan->name;
        });

        static::updating(function ($perwakilan) {
            $perwakilan->full_name = $perwakilan->type ." ". $perwakilan->name;
        });
    }
}
