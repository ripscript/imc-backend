<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsClassificationPemohon extends Model
{
    use HasFactory;
    protected $table = 'ms_classification_pemohon';

    const NAME_PERORANGAN = 'Perorangan';

    public static function getPeroranganId()
    {
        return cache()->rememberForever('classification_pemohon_perorangan_id', function () {
            return self::where('name', self::NAME_PERORANGAN)->value('id');
        });
    }
}
