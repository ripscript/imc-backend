<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Permohonan extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'permohonan';
    protected $guarded = ['id'];

    const JUMLAH_HARI_PROSES = 10;
    const JUMLAH_HARI_TAMBAHAN = 7;

    const STATUS = [
        'DIAJUKAN' => 0,
        'DIPROSES' => 1,
        'DIJAWAB' => 2,
        'DITOLAK' => 3,
    ];

    const STATUS_TEXT = [
        0 => 'DIAJUKAN',
        1 => 'DIPROSES',
        2 => 'DIJAWAB',
        3 => 'DITOLAK',
    ];

    public function pemohon()
    {
        return $this->belongsTo(Pemohon::class, 'pemohon_id');
    }

    // public function destinationInstitution()
    // {
    //     return $this->morphTo();
    // }

    public function institutions()
    {
        return $this->hasMany(PermohonanDestinationInstitution::class);
    }

    public function aksesData()
    {
        return $this->hasMany(PermohonanAksesData::class);
    }

    public function formatData()
    {
        return $this->hasMany(PermohonanFormatData::class);
    }

    public function salinanInformasi()
    {
        return $this->hasMany(PermohonanSalinanInformasi::class);
    }

    public function tanggapan()
    {
        return $this->hasMany(PermohonanTanggapan::class);
    }

    public function keberatan()
    {
        return $this->hasOne(Keberatan::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    protected static function booted()
    {
        static::creating(function ($permohonan) {
            $permohonan->number = self::generateNumber();
        });
    }

    protected static function generateNumber()
    {
        return DB::transaction(function () {
            $now = now();
            $month = $now->format('m');
            $year = $now->format('Y');

            // Lock table agar tidak race condition
            DB::statement('LOCK TABLE permohonan IN EXCLUSIVE MODE');

            $countThisMonth = self::withTrashed()->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $prefix = str_pad($countThisMonth + 1, 3, '0', STR_PAD_LEFT);

            return "{$prefix}/{$month}/{$year}/FPI";
        });
    }

    public static function findByNumber($number)
    {
        return static::where('number', $number)->first();
    }

    public function getDestinationInstitutionNameAttribute()
    {
        return $this->destinationInstitution?->name ?? null;
    }

    public function syncStatusFromInstitutions()
    {
        $statuses = $this->institutions()->pluck('status');

        if ($statuses->every(fn($s) => $s === self::STATUS['DIJAWAB'])) {
            $this->update(['status' => self::STATUS['DIJAWAB']]);
            return;
        }

        if ($statuses->contains(self::STATUS['DIPROSES'])) {
            $this->update(['status' => self::STATUS['DIPROSES']]);
            return;
        }
    }

    public function scopeFilterByRole($query, $user)
    {
        if ($user->role->name === 'Admin Satker/Perwakilan') {
            $query->whereHas('institutions', function ($q) use ($user) {
                if ($user->perwakilan_id) {
                    $q->where('destination_institution_type', 'App\\Models\\MsPerwakilan')
                        ->where('destination_institution_id', $user->perwakilan_id);
                } elseif ($user->satuan_kerja_id) {
                    $q->where('destination_institution_type', 'App\\Models\\MsSatuanKerja')
                        ->where('destination_institution_id', $user->satuan_kerja_id);
                }
            });
        }

        return $query;
    }

    public function checkAccessInstitution()
    {
        $user = auth('admin')->user();
        if (!$user) {
            return false;
        }

        $role = $user->role->name;

        if (in_array($role, ['Superadmin', 'Admin Pusat'])) {
            return true;
        }

        if ($role !== 'Admin Satker/Perwakilan') {
            return false;
        }

        return $this->institutions()
            ->where(function ($q) use ($user) {
                $q->where(function ($q) use ($user) {
                    if ($user->perwakilan_id) {
                        $q->where('destination_institution_type', MsPerwakilan::class)
                        ->where('destination_institution_id', $user->perwakilan_id);
                    }
                })
                ->orWhere(function ($q) use ($user) {
                    if ($user->satuan_kerja_id) {
                        $q->where('destination_institution_type', MsSatuanKerja::class)
                        ->where('destination_institution_id', $user->satuan_kerja_id);
                    }
                });
            })
            ->exists();
    }
}
