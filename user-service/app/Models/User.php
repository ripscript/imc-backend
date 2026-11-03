<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasActivityLog, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guarded = ['id'];

    protected $logName = 'pengguna';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    const STATUS = [
        0 => 'Belum Terverifikasi',
        1 => 'Terverifikasi',
    ];

    const GENDER = [
        'LAKI_LAKI' => 1,
        'PEREMPUAN' => 2,
    ];

    public function role()
    {
        return $this->belongsTo(MsRole::class, 'role_id');
    }

    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class, 'user_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
