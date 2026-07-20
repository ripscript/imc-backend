<?php

namespace App\Traits;

use App\Models\Permohonan;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ApiResponse;

trait AuthorizesPemohon
{
    protected function authorizePemohon($permohonan, $pemohonId = null)
    {
        if (!($permohonan instanceof Permohonan)) {
            $permohonan = Permohonan::find($permohonan);
        }

        if (!$permohonan) {
            throw new HttpResponseException(ApiResponse::error('Permohonan tidak ditemukan', null, 404));
        }

        $pemohonId = $pemohonId ?? auth('pemohon')->id();

        if ($permohonan->pemohon_id !== $pemohonId) {
            throw new HttpResponseException(ApiResponse::error('Anda tidak memiliki akses ke permohonan ini', null, 403));
        }
    }
}
