<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanDestinationInstitution extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['name'];

    public function destinationInstitution()
    {
        return $this->morphTo();
    }

    public function tanggapan()
    {
        return $this->hasOne(PermohonanTanggapan::class, 'permohonan_destination_institution_id');
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

    public function getNameAttribute()
    {
        $map = [
            MsPerwakilan::class => 'full_name',
            MsSatuanKerja::class => 'name',
        ];

        $field = $map[$this->destination_institution_type] ?? null;

        return $field ? $this->destinationInstitution?->$field : null;
    }
}
