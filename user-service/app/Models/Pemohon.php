<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Pemohon extends Authenticatable implements JWTSubject
{
    use HasActivityLog, HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'pemohon';

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
        'token',
        'otp',
    ];

    const STATUS = [
        'BELUM_TERVERIFIKASI' => 0,
        'EMAIL_TERVERIFIKASI' => 1,
        'DATA_TERVERIFIKASI' => 2,
        'DIBLOKIR' => 3,
    ];

    const GENDER = [
        'LAKI_LAKI' => 1,
        'PEREMPUAN' => 2,
    ];

    public function classification()
    {
        return $this->belongsTo(MsClassificationPemohon::class, 'classification_pemohon_id');
    }

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

    protected static function booted()
    {
        static::creating(function ($pemohon) {
            $pemohon->registration_number = self::generateRegistrationNumber();
        });
    }

    protected static function generateRegistrationNumber()
    {
        $date = now()->format('dmy');
        $countToday = self::whereDate('created_at', now()->toDateString())->count();
        $prefix = str_pad($countToday + 1, 2, '0', STR_PAD_LEFT);

        return $prefix.$date.'REG';
    }

    public static function generateUniqueToken()
    {
        do {
            $token = Str::random(60);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    public static function generateOtp()
    {
        $otp = '';
        for ($i = 0; $i < 5; $i++) {
            $otp .= (string) random_int(0, 9);
        }

        return $otp;
    }

    public function getIsEmailVerifiedAttribute()
    {
        return ! is_null($this->email_verified_at);
    }
}
