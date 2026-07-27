<?php

namespace App\Services;

use App\Models\MsClassificationPemohon;

class ClassificationPemohonService
{
    public function index()
    {
        return MsClassificationPemohon::all();
    }
}
