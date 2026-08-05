<?php

namespace App\Services;

use App\Exceptions\Domain\EntityNotFoundException;
use App\Models\Content;
use App\Models\LatestInformation;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class HomeService
{
    const SLUG_BERANDA = 'beranda';

    public function index()
    {
        $beranda = Menu::findBySlug(self::SLUG_BERANDA);
        if (! $beranda) {
            throw new EntityNotFoundException('Beranda');
        }

        $contents = Content::where([
            'parent_type' => Menu::class,
            'parent_id' => $beranda->id,
            'is_section' => true,
        ])
            ->orderBy('order', 'asc')
            ->get();

        return $contents->map(function ($content) {
            return [
                'content_id' => md5($content->id),
                'type' => $content->type,
            ];
        });
    }

    public function detail($id)
    {
        $content = Content::whereRaw('MD5(id::text) = ?', [$id])
            ->select('id', 'section_title', 'type', 'order')
            ->first();
        if (! $content) {
            throw new EntityNotFoundException('Section');
        }

        $content->content = Content::find($content->id)->getDetailBasedOnType();

        return $content;
    }

    public function latestInformation()
    {
        return LatestInformation::select('title', 'url', 'publish_date')
            ->where('is_active', true)
            ->orderBy('publish_date', 'desc')
            ->get();
    }

    public function summaryPermohonan()
    {
        $totalPengunjung = DB::table('visitors')->count();
        $permohonan = DB::table('permohonan')->whereNull('deleted_at')->count();
        $permohonanSelesai = DB::table('permohonan')->whereNull('deleted_at')->whereIn('status', [2, 3])->count();

        return [
            'informasi_publik' => 0,
            'permohonan' => $permohonan,
            'permohonan_selesai' => $permohonanSelesai,
            'pengunjung' => $totalPengunjung,
        ];
    }

    public function sifatInformasi()
    {
        return DB::table('ms_sifat_informasi')
            ->select('name', 'icon_path', 'description', 'link')
            ->whereNull('deleted_at')
            ->get();
    }
}
