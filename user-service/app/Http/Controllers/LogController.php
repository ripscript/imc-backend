<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ChatLog;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    public function activity(Request $request)
    {
        try {
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $limit = (int) $request->input('limit', 10);
            $page = max((int) $request->input('page', 1), 1);
    
            $subjectMappings = [
                'ChildSubMenu' => ['table' => 'child_sub_menus', 'field' => 'title'],
                'ContentDetailAgenda' => ['table' => 'content_detail_agendas', 'field' => 'event_name'],
                'ContentDetailGallery' => ['table' => 'content_detail_galleries', 'field' => 'title'],
                'ContentDetailPublication' => ['table' => 'content_detail_publications', 'field' => 'title'],
                'ContentDetailRepository' => ['table' => 'content_detail_repositories', 'field' => 'title'],
                'Content' => ['table' => 'contents', 'field' => 'section_title'],
                'Keberatan' => ['table' => 'keberatan', 'field' => 'number'],
                'LatestInformation' => ['table' => 'latest_informations', 'field' => 'title'],
                'Menu' => ['table' => 'menus', 'field' => 'title'],
                'MsAlasanKeberatan' => ['table' => 'ms_alasan_keberatan', 'field' => 'name'],
                'MsFaq' => ['table' => 'ms_faq', 'field' => 'title'],
                'MsKategoriContent' => ['table' => 'ms_kategori_contents', 'field' => 'title'],
                'MsKategoriFaq' => ['table' => 'ms_kategori_faq', 'field' => 'name'],
                'MsNegara' => ['table' => 'ms_negara', 'field' => 'name'],
                'MsPerwakilan' => ['table' => 'ms_perwakilan', 'field' => 'name'],
                'MsSatuanKerja' => ['table' => 'ms_satuan_kerja', 'field' => 'name'],
                'MsSifatInformasi' => ['table' => 'ms_sifat_informasi', 'field' => 'name'],
                'MsTag' => ['table' => 'ms_tags', 'field' => 'title'],
                'Pemohon' => ['table' => 'pemohon', 'field' => 'name'],
                'Permohonan' => ['table' => 'permohonan', 'field' => 'number'],
                'SubMenu' => ['table' => 'sub_menus', 'field' => 'title'],
                'User' => ['table' => 'users', 'field' => 'name'],
                'MsHoliday' => ['table' => 'ms_holidays', 'field' => 'name']
            ];
    
            $query = DB::table('activity_log')
                ->select(
                    'activity_log.id',
                    'activity_log.description',
                    'activity_log.subject_type',
                    'activity_log.subject_id',
                    'activity_log.causer_type',
                    'activity_log.causer_id',
                    'activity_log.created_at',
                    DB::raw('COALESCE(users.name, pemohon.name) as causer_name')
                )
                ->leftJoin('users', function ($join) {
                    $join->on('activity_log.causer_id', '=', 'users.id')
                        ->where('activity_log.causer_type', '=', 'App\\Models\\User');
                })
                ->leftJoin('pemohon', function ($join) {
                    $join->on('activity_log.causer_id', '=', 'pemohon.id')
                        ->where('activity_log.causer_type', '=', 'App\\Models\\Pemohon');
                });
            
            if ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('activity_log.description', 'ilike', "%{$search}%")
                        ->orWhere('users.name', 'ilike', "%{$search}%")
                        ->orWhere('pemohon.name', 'ilike', "%{$search}%");
                });
            }
    
            if ($sortBy && $sortOrder) {
                $query->orderBy("activity_log.$sortBy", $sortOrder);
            } else {
                $query->orderBy('activity_log.created_at', 'desc');
            }
    
            $total = $query->count();
            $logs  = $query->forPage($page, $limit)->get();
    
            $logs->transform(function ($log) use ($subjectMappings) {
                $subjectName = null;
                if ($log->subject_type && $log->subject_id) {
                    $subjectType = class_basename($log->subject_type);
                    if (isset($subjectMappings[$subjectType])) {
                        $map = $subjectMappings[$subjectType];
                        $subjectName = DB::table($map['table'])
                            ->where('id', $log->subject_id)
                            ->value($map['field']);
                    }
                }
    
                $causerType = class_basename($log->causer_type);
                $causerName = $log->causer_name ? $causerType." - ".$log->causer_name : "";
    
                return [
                    'description' => $log->description,
                    'causer' => $causerName,
                    'subject_name' => $subjectName,
                    'created_at' => $log->created_at,
                ];
            });
    
            $response = [
                'data' => $logs,
                'meta' => [
                    'total' => $total,
                    'page'  => $page,
                    'limit' => $limit,
                    'total_page' => $limit > 0 ? (int) ceil($total / $limit) : 0,
                ],
            ];
    
            return ApiResponse::success($response, 'Berhasil mengambil daftar aktivitas');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function chat(Request $request)
    {
        try {
            $query = DB::table('chat_sessions')
                ->leftJoin('pemohon', 'chat_sessions.pemohon_id', '=', 'pemohon.id')
                ->select(
                    'chat_sessions.uuid as session',
                    'chat_sessions.started_at',
                    'chat_sessions.ended_at',
                    'chat_sessions.last_activity_at',
                    'chat_sessions.is_active',
                    'chat_sessions.created_at',
                    DB::raw("COALESCE(pemohon.name, 'Anonim') AS pemohon_name")
                )
                ->orderBy('created_at', 'desc');
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['chat_sessions.uuid', DB::raw("COALESCE(pemohon.name, 'Anonim')")], 'chat_sessions.updated_at');

            return ApiResponse::success($data, 'Berhasil mengambil data log chatbot');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function chatDetail($session)
    {
        try {
            $chatSession = ChatSession::where('uuid', $session)->firstOrFail();
            $log = ChatLog::where('chat_session_id', $chatSession->id)
                ->orderBy('created_at', 'asc')
                ->get();

            return ApiResponse::success([
                'chat_session' => $chatSession,
                'log' => $log
            ], 'Berhasil mengambil detail chat');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

}
