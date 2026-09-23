<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['project_no', 'client_id', 'partner_id', 'category_id', 'workflow_id', 'pic_user_id', 'status', 'progress', 'target_end_date'] as $column) {
            if (! Schema::hasColumn('content_detail_projects', $column)) {
                Schema::table('content_detail_projects', function (Blueprint $table) use ($column) {
                    match ($column) {
                        'project_no' => $table->string('project_no')->nullable()->unique(),
                        'client_id' => $table->unsignedBigInteger('client_id')->nullable(),
                        'partner_id' => $table->unsignedBigInteger('partner_id')->nullable(),
                        'category_id' => $table->unsignedBigInteger('category_id')->nullable(),
                        'workflow_id' => $table->unsignedBigInteger('workflow_id')->nullable(),
                        'pic_user_id' => $table->unsignedBigInteger('pic_user_id')->nullable(),
                        'status' => $table->string('status', 20)->default('draft'),
                        'progress' => $table->unsignedSmallInteger('progress')->default(0),
                        'target_end_date' => $table->date('target_end_date')->nullable(),
                    };
                });
            }
        }

        if (! Schema::hasColumn('content_detail_projects', 'deleted_at')) {
            Schema::table('content_detail_projects', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        Schema::table('content_detail_projects', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('pm_clients')->nullOnDelete();
            $table->foreign('partner_id')->references('id')->on('pm_partners')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('pm_project_categories')->nullOnDelete();
            $table->foreign('workflow_id')->references('id')->on('pm_workflows')->nullOnDelete();
            $table->foreign('pic_user_id')->references('id')->on('users')->nullOnDelete();
        });

        if (! Schema::hasTable('projects')) {
            return;
        }

        $contentId = DB::table('contents')->where('type', 'project')->orderBy('id')->value('id')
            ?? DB::table('contents')->orderBy('id')->value('id');

        $idMap = [];
        if ($contentId) {
            $projects = DB::table('projects')->get();
            foreach ($projects as $project) {
                $newId = DB::table('content_detail_projects')->insertGetId([
                    'content_id' => $contentId,
                    'title' => $project->name,
                    'slug' => Str::slug($project->project_no ?? $project->name),
                    'description' => $project->description,
                    'is_publicated' => $project->status === 'active',
                    'publish_date' => $project->start_date,
                    'start_date' => $project->start_date,
                    'project_no' => $project->project_no,
                    'client_id' => $project->client_id,
                    'partner_id' => $project->partner_id,
                    'category_id' => $project->category_id,
                    'workflow_id' => $project->workflow_id,
                    'pic_user_id' => $project->pic_user_id,
                    'status' => $project->status,
                    'progress' => $project->progress,
                    'target_end_date' => $project->target_end_date,
                    'created_by' => $project->created_by,
                    'updated_by' => $project->updated_by,
                    'created_at' => $project->created_at,
                    'updated_at' => $project->updated_at,
                    'deleted_at' => $project->deleted_at,
                ]);
                $idMap[$project->id] = $newId;
            }
        }

        foreach ($idMap as $oldId => $newId) {
            DB::table('project_attachments')->where('project_id', $oldId)->update(['project_id' => $newId]);
        }

        Schema::table('project_attachments', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->foreign('project_id')->references('id')->on('content_detail_projects')->cascadeOnDelete();
        });

        Schema::dropIfExists('projects');
    }

    public function down(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_no')->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('workflow_id')->nullable();
            $table->unsignedBigInteger('pic_user_id')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedSmallInteger('progress')->default(0);
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('pm_clients')->nullOnDelete();
            $table->foreign('partner_id')->references('id')->on('pm_partners')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('pm_project_categories')->nullOnDelete();
            $table->foreign('workflow_id')->references('id')->on('pm_workflows')->nullOnDelete();
            $table->foreign('pic_user_id')->references('id')->on('users')->nullOnDelete();
        });

        $idMap = [];
        $rows = DB::table('content_detail_projects')->whereNotNull('project_no')->get();
        foreach ($rows as $row) {
            $newId = DB::table('projects')->insertGetId([
                'project_no' => $row->project_no,
                'name' => $row->title,
                'description' => $row->description,
                'client_id' => $row->client_id,
                'partner_id' => $row->partner_id,
                'category_id' => $row->category_id,
                'workflow_id' => $row->workflow_id,
                'pic_user_id' => $row->pic_user_id,
                'status' => $row->status,
                'progress' => $row->progress,
                'start_date' => $row->start_date,
                'target_end_date' => $row->target_end_date,
                'created_by' => $row->created_by,
                'updated_by' => $row->updated_by,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ]);
            $idMap[$row->id] = $newId;
        }

        foreach ($idMap as $oldId => $newId) {
            DB::table('project_attachments')->where('project_id', $oldId)->update(['project_id' => $newId]);
        }

        Schema::table('project_attachments', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });

        Schema::table('content_detail_projects', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['partner_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['workflow_id']);
            $table->dropForeign(['pic_user_id']);
        });
    }
};
