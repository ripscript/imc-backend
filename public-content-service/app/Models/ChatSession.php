<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatSession extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function logs()
    {
        return $this->hasMany(ChatLog::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            do {
                $uuid = (string) Str::uuid();
            } while (self::where('uuid', $uuid)->exists());

            $model->uuid = $uuid;
        });
    }
}
