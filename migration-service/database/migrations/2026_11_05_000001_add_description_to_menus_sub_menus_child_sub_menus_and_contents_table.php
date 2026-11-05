<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('menus') && ! Schema::hasColumn('menus', 'description')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->string('description', 255)->nullable()->after('title');
            });
        }

        if (Schema::hasTable('sub_menus') && ! Schema::hasColumn('sub_menus', 'description')) {
            Schema::table('sub_menus', function (Blueprint $table) {
                $table->string('description', 255)->nullable()->after('title');
            });
        }

        if (Schema::hasTable('child_sub_menus') && ! Schema::hasColumn('child_sub_menus', 'description')) {
            Schema::table('child_sub_menus', function (Blueprint $table) {
                $table->string('description', 255)->nullable()->after('title');
            });
        }

        if (Schema::hasTable('contents') && ! Schema::hasColumn('contents', 'section_description')) {
            Schema::table('contents', function (Blueprint $table) {
                $table->string('section_description', 255)->nullable()->after('section_title');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('contents') && Schema::hasColumn('contents', 'section_description')) {
            Schema::table('contents', function (Blueprint $table) {
                $table->dropColumn('section_description');
            });
        }

        if (Schema::hasTable('child_sub_menus') && Schema::hasColumn('child_sub_menus', 'description')) {
            Schema::table('child_sub_menus', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }

        if (Schema::hasTable('sub_menus') && Schema::hasColumn('sub_menus', 'description')) {
            Schema::table('sub_menus', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }

        if (Schema::hasTable('menus') && Schema::hasColumn('menus', 'description')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
