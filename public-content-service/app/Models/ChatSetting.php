<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'opening',
        'closing',
        'to_whatsapp',
        'another_question',
        'confirmation',
    ];
}
