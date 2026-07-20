<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\LogActivity;
use App\Models\Comment;
use App\Models\Keberatan;
use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    CONST LOG_NAME = 'komentar';

    public function index(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:permohonan,keberatan',
                'id'   => 'required|integer',
            ]);

            $allowedTypes = [
                'permohonan' => Permohonan::class,
                'keberatan'  => Keberatan::class,
            ];

            if (!isset($allowedTypes[$request->type])) {
                DB::rollBack();
                return ApiResponse::error('Tipe tidak valid', '', 422);
            }

            $type = $allowedTypes[$request->type];
            $content = $type::find($request->id);

            if (!$content) {
                DB::rollBack();
                return ApiResponse::error(ucfirst($request->type) . ' tidak ditemukan', '', 422);
            }

            $comments = $content->comments()
                ->whereNull('parent_id')
                ->orderBy('created_at', 'asc')
                ->get();

            $tree = $this->buildCommentTree($comments);

            return ApiResponse::success($tree, 'Berhasil mengambil seluruh komentar');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function buildCommentTree($comments)
    {
        return $comments->map(function ($comment) {
            return [
                'id'         => $comment->id,
                'comment'    => $comment->comment,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'user_name'  => $comment->user?->name,
                'children'   => $comment->children->count() ? $this->buildCommentTree($comment->children) : [],
            ];
        });
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'type' => 'required|in:permohonan,keberatan',
                'id' => 'required|integer',
                'comment' => 'required|string',
                'parent_id' => 'nullable|integer'
            ]);

            $allowedTypes = [
                'permohonan' => Permohonan::class,
                'keberatan'  => Keberatan::class,
            ];

            if (!isset($allowedTypes[$request->type])) {
                DB::rollBack();
                return ApiResponse::error('Tipe tidak valid', '', 422);
            }

            $type = $allowedTypes[$request->type];
            $content = $type::find($request->id);

            if (!$content) {
                DB::rollBack();
                return ApiResponse::error(ucfirst($request->type) . ' tidak ditemukan', '', 422);
            }

            if ($request->filled('parent_id')) {
                $parentComment = Comment::where([
                    'commentable_type' => $type,
                    'commentable_id' => $content->id,
                    'id' => $request->parent_id
                ])->first();

                if (!$parentComment) {
                    DB::rollBack();
                    return ApiResponse::error('Parent komentar tidak valid', '', 400);
                }
            }

            $comment = $content->comments()->create([
                'user_id' => auth('admin')->id(),
                'comment' => $request->comment,
                'parent_id' => $request->parent_id
            ]);

            LogActivity::create(self::LOG_NAME, $content, 'create', auth('admin')->user());

            DB::commit();
            return ApiResponse::success($comment, 'Berhasil menambahkan komentar', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $comment = Comment::findOrfail($id);

            $request->validate([
                'comment' => 'required|string',
            ]);

            $comment->comment = $request->comment;
            $comment->save();

            LogActivity::create(self::LOG_NAME, $comment->commentable, 'update', auth('admin')->user());
            
            DB::commit();
            return ApiResponse::success($comment, 'Berhasil memperbarui komentar');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Comment::findOrFail($id);
            $data->delete();

            LogActivity::create(self::LOG_NAME, $data->commentable, 'delete', auth('admin')->user());

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus komentar');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
