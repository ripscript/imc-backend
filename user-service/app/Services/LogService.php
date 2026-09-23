<?php

namespace App\Services;

use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class LogService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function activity(QueryFilterDTO $filter)
    {
        $subjectMappings = [
            'ChildSubMenu' => ['table' => 'child_sub_menus', 'field' => 'title'],
            'ContentDetailAgenda' => ['table' => 'content_detail_agendas', 'field' => 'event_name'],
            'ContentDetailGallery' => ['table' => 'content_detail_galleries', 'field' => 'title'],
            'ContentDetailPublication' => ['table' => 'content_detail_publications', 'field' => 'title'],
            'ContentDetailRepository' => ['table' => 'content_detail_repositories', 'field' => 'title'],
            'Content' => ['table' => 'contents', 'field' => 'section_title'],
            'LatestInformation' => ['table' => 'latest_informations', 'field' => 'title'],
            'Menu' => ['table' => 'menus', 'field' => 'title'],
            'MsFaq' => ['table' => 'ms_faq', 'field' => 'title'],
            'MsKategoriContent' => ['table' => 'ms_kategori_contents', 'field' => 'title'],
            'MsKategoriFaq' => ['table' => 'ms_kategori_faq', 'field' => 'name'],
            'MsNegara' => ['table' => 'ms_negara', 'field' => 'name'],
            'MsPerwakilan' => ['table' => 'ms_perwakilan', 'field' => 'name'],
            'MsSatuanKerja' => ['table' => 'ms_satuan_kerja', 'field' => 'name'],
            'MsSifatInformasi' => ['table' => 'ms_sifat_informasi', 'field' => 'name'],
            'MsTag' => ['table' => 'ms_tags', 'field' => 'title'],
            'SubMenu' => ['table' => 'sub_menus', 'field' => 'title'],
            'User' => ['table' => 'users', 'field' => 'name'],
            'MsHoliday' => ['table' => 'ms_holidays', 'field' => 'name'],
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
                'users.name as causer_name'
            )
            ->leftJoin('users', function ($join) {
                $join->on('activity_log.causer_id', '=', 'users.id')
                    ->where('activity_log.causer_type', '=', 'App\\Models\\User');
            });

        if ($filter->search) {
            $search = $filter->search;
            $query->where(function ($sub) use ($search) {
                $sub->where('activity_log.description', 'ilike', "%{$search}%")
                    ->orWhere('users.name', 'ilike', "%{$search}%");
            });
        }

        $sortBy = $filter->sort_by ?: 'created_at';
        $sortOrder = $filter->sort_order ?: 'desc';
        $query->orderBy("activity_log.$sortBy", $sortOrder);

        $limit = (int) ($filter->limit ?: 10);
        $page = max($filter->page, 1);

        $total = $query->count();
        $logs = $query->forPage($page, $limit)->get();

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
            $causerName = $log->causer_name ? $causerType.' - '.$log->causer_name : '';

            return [
                'description' => $log->description,
                'causer' => $causerName,
                'subject_name' => $subjectName,
                'created_at' => $log->created_at,
            ];
        });

        return [
            'data' => $logs,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_page' => $limit > 0 ? (int) ceil($total / $limit) : 0,
            ],
        ];
    }
}
