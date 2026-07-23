<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SettingService
{
    public function index()
    {
        return DB::table('site_settings')
            ->select(
                'address',
                'phone',
                'email',
                'facebook_url',
                'instagram_url',
                'youtube_url',
                'twitter_url'
            )
            ->first();
    }
}
