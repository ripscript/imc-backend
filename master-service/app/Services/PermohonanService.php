<?php

namespace App\Services;

use App\Models\MsPermohonanAksesData;
use App\Models\MsPermohonanFormatData;
use App\Models\MsPermohonanSalinanInformasi;

class PermohonanService
{
    public function aksesData()
    {
        return MsPermohonanAksesData::all();
    }

    public function formatData()
    {
        return MsPermohonanFormatData::all();
    }

    public function salinanInformasi()
    {
        return MsPermohonanSalinanInformasi::all();
    }
}
