<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $table = 'company_profiles';

    protected $guarded = ['id'];

    protected $casts = [
        'token_expired_at' => 'datetime',
        'tgl_daftar_siinas' => 'datetime',
        'alamat_kantor' => 'array',
        'alamat_pabrik' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
