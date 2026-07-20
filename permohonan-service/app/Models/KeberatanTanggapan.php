<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KeberatanTanggapan extends Model
{
    use HasFactory;
    protected $table = 'keberatan_tanggapan';
    protected $guarded = ['id'];

    protected static function generateNumber()
    {
        return DB::transaction(function () {
            $now = now();
            $month = $now->format('m');
            $year = $now->format('Y');

            // Lock table agar tidak race condition
            DB::statement('LOCK TABLE keberatan_tanggapan IN EXCLUSIVE MODE');
    
            $countThisMonth = self::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();
    
            $prefix = str_pad($countThisMonth + 1, 3, '0', STR_PAD_LEFT);
    
            return "{$prefix}/{$month}/{$year}/FPTK";
        });
    }
}
