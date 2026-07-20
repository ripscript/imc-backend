<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Content;
use App\Models\LatestInformation;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    const SLUG_BERANDA = 'beranda';

    public function index()
    {
        try {
            $beranda = Menu::findBySlug(self::SLUG_BERANDA);
            if (!$beranda) {
                return ApiResponse::error('Beranda tidak ditemukan', 404);
            }

            $contents = Content::where([
                'parent_type' => Menu::class,
                'parent_id' => $beranda->id,
                'is_section' => true
            ])
            ->orderBy('order', 'asc')
            ->get();

            $data = $contents->map(function ($content) {
                return [
                    'content_id' => md5($content->id),
                    'type' => $content->type
                ];
            });

            return ApiResponse::success($data, 'Berhasil mengambil list section beranda');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $content = Content::whereRaw('MD5(id::text) = ?', [$id])
                ->select('id', 'section_title', 'type', 'order')
                ->first();
            if (!$content) {
                return ApiResponse::error('Section tidak ditemukan', '', 404);
            }

            $content->content = Content::find($content->id)->getDetailBasedOnType();

            return ApiResponse::success($content, 'Berhasil mengambil detail section');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function latestInformation()
    {
        try {
            $content = LatestInformation::select('title', 'url', 'publish_date')
                ->where('is_active', true)
                ->orderBy('publish_date', 'desc')
                ->get();

            return ApiResponse::success($content, 'Berhasil mengambil data informasi terkini');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function summaryPermohonan()
    {
        try {
            // $totalPengunjung = DB::table('visitors')
            //     ->select(DB::raw('COUNT(DISTINCT CONCAT(ip_address, user_agent, device_type)) as total'))
            //     ->value('total');
            $totalPengunjung = DB::table('visitors')->count();
            $permohonan = DB::table('permohonan')->whereNull('deleted_at')->count();
            $permohonanSelesai = DB::table('permohonan')->whereNull('deleted_at')->whereIn('status', [2, 3])->count();

            $data = [
                'informasi_publik' => 0,
                'permohonan' => $permohonan,
                'permohonan_selesai' => $permohonanSelesai,
                'pengunjung' => $totalPengunjung
            ];

            return ApiResponse::success($data, 'Berhasil mengambil data summary permohonan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function sifatInformasi()
    {
        try {
            $data = DB::table('ms_sifat_informasi')
                ->select('name', 'icon_path', 'description', 'link')
                ->whereNull('deleted_at')
                ->get();

            return ApiResponse::success($data, 'Berhasil mengambil data sifat informasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
