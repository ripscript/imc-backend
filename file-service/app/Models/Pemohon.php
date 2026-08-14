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

    protected $table = 'pemohon';

    protected $fillable = [
        'registration_number',
        'name',
        'email',
        'email_verified_at',
        'identity_number',
        'gender',
        'classification_pemohon_id',
        'organization',
        'job_title',
        'username',
        'phone_number',
        'address',
        'status',
        'token',
        'password',
        'last_login_at',
        'created_by',
        'updated_by',
        'otp',
        'otp_created_at',
        'identity_card_file_path',
        'legal_document_file_path',
        'token_version',
        'is_privacy_policy_accepted',
    ];

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
