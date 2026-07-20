<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PermohonanTanggapan extends Model
{
    use HasFactory;
    protected $table = 'permohonan_tanggapan';
    protected $guarded = ['id'];
    protected $appends = ['institution_name'];

    public function permohonanDestinationInstitution()
    {
        return $this->belongsTo(PermohonanDestinationInstitution::class, 'permohonan_destination_institution_id');
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

    protected static function generateNumber()
    {
        return DB::transaction(function () {
            $now = now();
            $month = $now->format('m');
            $year = $now->format('Y');

            // Lock table agar tidak race condition
            DB::statement('LOCK TABLE permohonan_tanggapan IN EXCLUSIVE MODE');
    
            $countThisMonth = self::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();
    
            $prefix = str_pad($countThisMonth + 1, 3, '0', STR_PAD_LEFT);
    
            return "{$prefix}/{$month}/{$year}/FPT";
        });
    }

    public function getInstitutionNameAttribute()
    {
        return $this->permohonanDestinationInstitution->name ?? null;
    }

    public function getCanAnswerAttribute()
    {
        $user = auth('admin')->user();
        if (!$user) {
            return false;
        }

        $role = $user->role->name;
        $destination = $this->permohonanDestinationInstitution;

        if (!$destination) {
            return false;
        }

        if (in_array($role, ['Superadmin', 'Admin Pusat'])) {
            return true;
        }

        if ($role == 'Admin Satker/Perwakilan') {

            $type = $destination->destination_institution_type;
            $id = $destination->destination_institution_id;

            if ($user->perwakilan_id && $type == MsPerwakilan::class && $id == $user->perwakilan_id) {
                return true;
            }

            if ($user->satuan_kerja_id && $type == MsSatuanKerja::class && $id == $user->satuan_kerja_id) {
                return true;
            }
        }

        return false;
    }

}
