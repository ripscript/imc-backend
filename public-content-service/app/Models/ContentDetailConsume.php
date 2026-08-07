<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContentDetailConsume extends Model
{
    use HasFactory;
    protected $fillable = [
        'content_id',
        'consume_id',
        'consume_type',
        'order',
        'created_by',
        'updated_by',
    ];
    protected $hidden = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content_id', 'consume_id'];

    protected $appends = ['list_content', 'parent_menu'];

    public function getListContentAttribute()
    {
        switch ($this->consume_type) {
            case 'publication':
                $query = DB::table('content_detail_publications')
                    ->where('content_id', $this->consume_id)
                    ->where('is_publicated', true)
                    ->select(
                        'title',
                        'slug',
                        'thumbnail_path',
                        'publish_date'
                    )
                    ->orderBy('publish_date', 'desc')
                    ->limit(5)
                    ->get();

                return $query;
                break;

            case 'gallery':
                $query = DB::table('content_detail_galleries as g')
                    ->where('g.content_id', $this->consume_id)
                    ->where('g.is_publicated', true)
                    ->select(
                        'g.title',
                        'g.slug',
                        'g.publish_date'
                    )
                    ->selectSub(function ($q) {
                        $q->from('content_detail_gallery_lists as l')
                            ->select('l.image_path')
                            ->whereRaw('l.content_detail_gallery_id = g.id')
                            ->orderBy('l.order', 'asc')
                            ->limit(1);
                    }, 'first_image_path')
                    ->orderBy('g.publish_date', 'desc')
                    ->limit(5)
                    ->get();

                return $query;
                break;

            case 'repository':
                $query = DB::table('content_detail_repositories')
                    ->where('content_id', $this->consume_id)
                    ->where('is_publicated', true)
                    ->select(
                        'title',
                        'slug',
                        'thumbnail_path',
                        'publish_date'
                    )
                    ->orderBy('publish_date', 'desc')
                    ->limit(5)
                    ->get();

                return $query;
                break;

            case 'project':
                $query = DB::table('content_detail_projects')
                    ->where('content_id', $this->consume_id)
                    ->where('is_publicated', true)
                    ->select(
                        'title',
                        'slug',
                        'thumbnail_path',
                        'publish_date'
                    )
                    ->orderBy('publish_date', 'desc')
                    ->limit(5)
                    ->get();

                return $query;
                break;

            default:
                return [];
                break;
        }
    }

    public function getParentMenuAttribute()
    {
        $content = Content::find($this->consume_id);
        if (! $content) {
            return null;
        }

        return $content->parent_slug;
    }
}
