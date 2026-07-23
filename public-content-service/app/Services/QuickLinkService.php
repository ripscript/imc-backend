<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class QuickLinkService
{
    public function index()
    {
        return DB::table('quick_links')
            ->select('title', 'url', 'order')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }
}
