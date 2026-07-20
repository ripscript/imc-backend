<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeberatanAlasan extends Model
{
    use HasFactory;
    protected $table = 'keberatan_alasan';
    protected $guarded = ['id'];

    public function master()
    {
        return $this->belongsTo(MsAlasanKeberatan::class, 'alasan_keberatan_id');
    }
}
