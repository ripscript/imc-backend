<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Pemohon extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable, SoftDeletes;
    protected $table = 'pemohon';

    protected $hidden = [
        'password',
        'remember_token',
        'token',
        'otp'
    ];

    const STATUS = [
        'BELUM_TERVERIFIKASI' => 0,
        'EMAIL_TERVERIFIKASI' => 1,
        'DATA_TERVERIFIKASI' => 2,
        'DIBLOKIR' => 3
    ];

    const GENDER = [
        'LAKI_LAKI' => 1,
        'PEREMPUAN' => 2
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'token_version' => $this->token_version,
        ];
    }

    public function getIsEmailVerifiedAttribute()
    {
        return !is_null($this->email_verified_at);
    }
}
