<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Keberatan extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'keberatan';
    protected $guarded = ['id'];

    const JUMLAH_HARI_PROSES = 30;

    const STATUS = [
        'DIAJUKAN' => 0,
        'DIPROSES' => 1,
        'DIJAWAB' => 2
    ];

    const STATUS_TEXT = [
        0 => 'DIAJUKAN',
        1 => 'DIPROSES',
        2 => 'DIJAWAB',
    ];

    public function pemohon()
    {
        return $this->belongsTo(Pemohon::class, 'pemohon_id');
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

    public function alasanKeberatan()
    {
        return $this->hasMany(KeberatanAlasan::class);
    }

    public function tanggapan()
    {
        return $this->hasOne(KeberatanTanggapan::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    protected static function booted()
    {
        static::creating(function ($keberatan) {
            $keberatan->number = self::generateNumber();
        });
    }

    protected static function generateNumber()
    {
        return DB::transaction(function () {
            $now = now();
            $month = $now->format('m');
            $year = $now->format('Y');

            // Lock table agar tidak race condition
            DB::statement('LOCK TABLE keberatan IN EXCLUSIVE MODE');
    
            $countThisMonth = self::withTrashed()->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();
    
            $prefix = str_pad($countThisMonth + 1, 3, '0', STR_PAD_LEFT);
    
            return "{$prefix}/{$month}/{$year}/FKI";
        });
    }

    public static function findByNumber($number)
    {
        return static::where('number', $number)->first();
    }

    public function scopeFilterByRole($query, $user)
    {
        if ($user->role->name === 'Admin Satker/Perwakilan') {
            $query->whereHas('permohonan', function ($q) use ($user) {
                $q->filterByRole($user);
            });
        }

        return $query;
    }
}
