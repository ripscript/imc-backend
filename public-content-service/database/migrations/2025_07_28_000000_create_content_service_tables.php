<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ms_roles')) {
            Schema::create('ms_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('role_id')->nullable()->references('id')->on('ms_roles');
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('ms_layout')) {
            Schema::create('ms_layout', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type');
                $table->boolean('is_custom')->default(false);
                $table->string('name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('menus')) {
            Schema::create('menus', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('description', 255)->nullable();
                $table->string('slug');
                $table->integer('order')->default(0);
                $table->boolean('has_sub')->default(false);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_dedicated')->default(false);
                $table->boolean('is_show_front')->default(false);
                $table->boolean('is_show_side_menu')->default(false);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('sub_menus')) {
            Schema::create('sub_menus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_id')->references('id')->on('menus')->onDelete('cascade');
                $table->string('title');
                $table->string('description', 255)->nullable();
                $table->string('slug');
                $table->integer('order')->default(0);
                $table->boolean('has_child')->default(false);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_show_side_menu')->default(false);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('child_sub_menus')) {
            Schema::create('child_sub_menus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sub_menu_id')->references('id')->on('sub_menus')->onDelete('cascade');
                $table->string('title');
                $table->string('description', 255)->nullable();
                $table->string('slug');
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('contents')) {
            Schema::create('contents', function (Blueprint $table) {
                $table->id();
                $table->string('parent_type');
                $table->unsignedBigInteger('parent_id');
                $table->string('type');
                $table->unsignedBigInteger('layout_id')->nullable();
                $table->string('section_title')->nullable();
                $table->string('section_description', 255)->nullable();
                $table->boolean('is_section')->default(false);
                $table->boolean('is_show_home')->nullable();
                $table->boolean('is_show_tag')->nullable();
                $table->string('sub_type')->nullable();
                $table->boolean('is_accessible')->default(true);
                $table->integer('order')->nullable();
                $table->boolean('has_additional_image')->default(false);
                $table->boolean('has_additional_video')->default(false);
                $table->boolean('has_additional_file')->default(false);
                $table->boolean('has_additional_link')->default(false);
                $table->boolean('has_additional_text')->default(false);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('ms_kategori_contents')) {
            Schema::create('ms_kategori_contents', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->boolean('is_active')->default(true);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ms_tags')) {
            Schema::create('ms_tags', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->boolean('is_active')->default(true);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_tags')) {
            Schema::create('content_tags', function (Blueprint $table) {
                $table->id();
                $table->string('content_type');
                $table->unsignedBigInteger('content_id');
                $table->unsignedBigInteger('tag_id');
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_publications')) {
            Schema::create('content_detail_publications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->string('slug');
                $table->text('headline')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_publicated')->default(true);
                $table->string('thumbnail_path')->nullable();
                $table->dateTime('publish_date')->nullable();
                $table->boolean('has_additional_image')->default(false);
                $table->boolean('has_additional_video')->default(false);
                $table->boolean('has_additional_file')->default(false);
                $table->boolean('has_additional_link')->default(false);
                $table->boolean('has_additional_text')->default(false);
                $table->unsignedBigInteger('layout_id')->nullable();
                $table->boolean('is_slider')->default(false);
                $table->unsignedBigInteger('kategori_content_id')->nullable();
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('content_detail_galleries')) {
            Schema::create('content_detail_galleries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->boolean('is_publicated')->default(true);
                $table->dateTime('publish_date')->nullable();
                $table->unsignedBigInteger('layout_id')->nullable();
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('content_detail_gallery_lists')) {
            Schema::create('content_detail_gallery_lists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_detail_gallery_id')->references('id')->on('content_detail_galleries')->onDelete('cascade');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('image_path')->nullable();
                $table->string('video_path')->nullable();
                $table->string('type')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_repositories')) {
            Schema::create('content_detail_repositories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->string('file_path')->nullable();
                $table->string('thumbnail_path')->nullable();
                $table->boolean('is_publicated')->default(true);
                $table->dateTime('publish_date')->nullable();
                $table->unsignedBigInteger('layout_id')->nullable();
                $table->integer('order')->nullable();
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('content_detail_agendas')) {
            Schema::create('content_detail_agendas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('event_date')->nullable();
                $table->string('time')->nullable();
                $table->string('location')->nullable();
                $table->boolean('is_publicated')->default(true);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('content_detail_projects')) {
            Schema::create('content_detail_projects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->string('thumbnail_path')->nullable();
                $table->boolean('is_publicated')->default(true);
                $table->dateTime('publish_date')->nullable();
                $table->unsignedBigInteger('layout_id')->nullable();
                $table->boolean('has_additional_image')->default(false);
                $table->boolean('has_additional_video')->default(false);
                $table->boolean('has_additional_file')->default(false);
                $table->boolean('has_additional_link')->default(false);
                $table->boolean('has_additional_text')->default(false);
                $table->string('duration')->nullable();
                $table->string('location')->nullable();
                $table->date('start_date')->nullable();
                $table->string('budget')->nullable();
                $table->string('organization_address')->nullable();
                $table->string('organization_name')->nullable();
                $table->unsignedBigInteger('kategori_content_id')->nullable();
                $table->string('project_no')->nullable()->unique();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->unsignedBigInteger('partner_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('workflow_id')->nullable();
                $table->unsignedBigInteger('pic_user_id')->nullable();
                $table->string('status', 20)->default('draft');
                $table->unsignedSmallInteger('progress')->default(0);
                $table->date('target_end_date')->nullable();
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('content_detail_showcases')) {
            Schema::create('content_detail_showcases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->string('thumbnail_path')->nullable();
                $table->boolean('is_publicated')->default(true);
                $table->dateTime('publish_date')->nullable();
                $table->unsignedBigInteger('layout_id')->nullable();
                $table->boolean('has_additional_image')->default(false);
                $table->boolean('has_additional_video')->default(false);
                $table->boolean('has_additional_file')->default(false);
                $table->boolean('has_additional_link')->default(false);
                $table->boolean('has_additional_text')->default(false);
                $table->string('duration')->nullable();
                $table->string('location')->nullable();
                $table->date('start_date')->nullable();
                $table->string('budget')->nullable();
                $table->string('organization_address')->nullable();
                $table->string('organization_name')->nullable();
                $table->unsignedBigInteger('kategori_content_id')->nullable();
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('content_detail_texts')) {
            Schema::create('content_detail_texts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->text('content')->nullable();
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_hero_carousels')) {
            Schema::create('content_detail_hero_carousels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('button_text')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_image_links')) {
            Schema::create('content_detail_image_links', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->string('image_path');
                $table->string('link')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_image_carousels')) {
            Schema::create('content_detail_image_carousels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->string('image_path');
                $table->text('description')->nullable();
                $table->string('link')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_video_carousels')) {
            Schema::create('content_detail_video_carousels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->string('type');
                $table->string('url')->nullable();
                $table->string('video_path')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_card_links')) {
            Schema::create('content_detail_card_links', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->string('color_code')->nullable();
                $table->string('link')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_accordion_groups')) {
            Schema::create('content_detail_accordion_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('title');
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_accordion_lists')) {
            Schema::create('content_detail_accordion_lists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_detail_accordion_group_id')->references('id')->on('content_detail_accordion_groups')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('file_path')->nullable();
                $table->string('link')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_consumes')) {
            Schema::create('content_detail_consumes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->unsignedBigInteger('consume_id');
                $table->string('consume_type');
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_organizational_structure_groups')) {
            Schema::create('content_detail_organizational_structure_groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->references('id')->on('contents')->onDelete('cascade');
                $table->string('name');
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_detail_organizational_structure_members')) {
            Schema::create('content_detail_organizational_structure_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_detail_organizational_structure_group_id')->references('id')->on('content_detail_organizational_structure_groups')->onDelete('cascade');
                $table->string('name');
                $table->integer('level')->default(1);
                $table->string('position')->nullable();
                $table->text('description')->nullable();
                $table->string('image_path')->nullable();
                $table->boolean('is_child')->default(false);
                $table->boolean('have_child')->default(false);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_additional_files')) {
            Schema::create('content_additional_files', function (Blueprint $table) {
                $table->id();
                $table->string('content_type');
                $table->unsignedBigInteger('content_id');
                $table->string('file_path');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_additional_images')) {
            Schema::create('content_additional_images', function (Blueprint $table) {
                $table->id();
                $table->string('content_type');
                $table->unsignedBigInteger('content_id');
                $table->string('image_path');
                $table->text('description')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_additional_videos')) {
            Schema::create('content_additional_videos', function (Blueprint $table) {
                $table->id();
                $table->string('content_type');
                $table->unsignedBigInteger('content_id');
                $table->string('title')->nullable();
                $table->string('type');
                $table->string('url')->nullable();
                $table->string('video_path')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_additional_links')) {
            Schema::create('content_additional_links', function (Blueprint $table) {
                $table->id();
                $table->string('content_type');
                $table->unsignedBigInteger('content_id');
                $table->string('link');
                $table->string('title')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_additional_texts')) {
            Schema::create('content_additional_texts', function (Blueprint $table) {
                $table->id();
                $table->string('content_type');
                $table->unsignedBigInteger('content_id');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->integer('order')->default(0);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('latest_informations')) {
            Schema::create('latest_informations', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->boolean('is_active')->default(true);
                $table->dateTime('publish_date')->nullable();
                $table->text('url')->nullable();
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('quick_links')) {
            Schema::create('quick_links', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('url');
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->text('value')->nullable();
                $table->integer('menu_max_show_front')->nullable()->default(5);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('activity_log')) {
            Schema::create('activity_log', function (Blueprint $table) {
                $table->id();
                $table->string('log_name')->nullable();
                $table->text('description');
                $table->nullableMorphs('subject', 'subject');
                $table->string('event')->nullable();
                $table->nullableMorphs('causer', 'causer');
                $table->text('properties')->nullable();
                $table->uuid('batch_uuid')->nullable();
                $table->timestamps();
                $table->index('log_name');
            });
        }

        if (! Schema::hasTable('global_search_index')) {
            Schema::create('global_search_index', function (Blueprint $table) {
                $table->id();
                $table->string('source_type');
                $table->unsignedBigInteger('source_id');
                $table->string('title')->nullable();
                $table->string('url')->nullable();
                $table->string('content_slug')->nullable();
                $table->text('search_vector')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('global_search_index');
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('quick_links');
        Schema::dropIfExists('latest_informations');
        Schema::dropIfExists('content_additional_texts');
        Schema::dropIfExists('content_additional_links');
        Schema::dropIfExists('content_additional_videos');
        Schema::dropIfExists('content_additional_images');
        Schema::dropIfExists('content_additional_files');
        Schema::dropIfExists('content_detail_organizational_structure_members');
        Schema::dropIfExists('content_detail_organizational_structure_groups');
        Schema::dropIfExists('content_detail_consumes');
        Schema::dropIfExists('content_detail_accordion_lists');
        Schema::dropIfExists('content_detail_accordion_groups');
        Schema::dropIfExists('content_detail_card_links');
        Schema::dropIfExists('content_detail_video_carousels');
        Schema::dropIfExists('content_detail_image_carousels');
        Schema::dropIfExists('content_detail_image_links');
        Schema::dropIfExists('content_detail_hero_carousels');
        Schema::dropIfExists('content_detail_texts');
        Schema::dropIfExists('content_detail_projects');
        Schema::dropIfExists('content_detail_agendas');
        Schema::dropIfExists('content_detail_repositories');
        Schema::dropIfExists('content_detail_gallery_lists');
        Schema::dropIfExists('content_detail_galleries');
        Schema::dropIfExists('content_detail_publications');
        Schema::dropIfExists('content_tags');
        Schema::dropIfExists('ms_tags');
        Schema::dropIfExists('ms_kategori_contents');
        Schema::dropIfExists('contents');
        Schema::dropIfExists('child_sub_menus');
        Schema::dropIfExists('sub_menus');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('ms_layout');
        Schema::dropIfExists('ms_roles');
    }
};
