<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Pemohon extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable, SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'token',
        'otp',
        'token_version',
        'remember_token',
        'created_by',
        'updated_by',
    ];
    protected $table = 'pemohon';

    protected $hidden = [
        'password',
        'remember_token',
        'token',
        'otp',
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
}
