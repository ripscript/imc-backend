<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // Fix pm_workflow_stages: drop full unique constraints and replace with partial indexes
            DB::statement('ALTER TABLE pm_workflow_stages DROP CONSTRAINT IF EXISTS pm_workflow_stages_workflow_id_sequence_unique');
            DB::statement('ALTER TABLE pm_workflow_stages DROP CONSTRAINT IF EXISTS pm_workflow_stages_workflow_id_code_unique');

            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS pm_workflow_stages_workflow_id_sequence_unique ON pm_workflow_stages (workflow_id, sequence) WHERE deleted_at IS NULL');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS pm_workflow_stages_workflow_id_code_unique ON pm_workflow_stages (workflow_id, code) WHERE deleted_at IS NULL');

            // Fix pm_project_stages: drop full unique constraint and replace with partial index
            DB::statement('ALTER TABLE pm_project_stages DROP CONSTRAINT IF EXISTS pm_project_stages_project_id_sequence_unique');

            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS pm_project_stages_project_id_sequence_unique ON pm_project_stages (project_id, sequence) WHERE deleted_at IS NULL');
        } elseif ($driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS pm_workflow_stages_workflow_id_sequence_unique ON pm_workflow_stages (workflow_id, sequence) WHERE deleted_at IS NULL');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS pm_workflow_stages_workflow_id_code_unique ON pm_workflow_stages (workflow_id, code) WHERE deleted_at IS NULL');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS pm_project_stages_project_id_sequence_unique ON pm_project_stages (project_id, sequence) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS pm_workflow_stages_workflow_id_sequence_unique');
            DB::statement('DROP INDEX IF EXISTS pm_workflow_stages_workflow_id_code_unique');

            Schema::table('pm_workflow_stages', function (Blueprint $table) {
                $table->unique(['workflow_id', 'sequence']);
                $table->unique(['workflow_id', 'code']);
            });

            DB::statement('DROP INDEX IF EXISTS pm_project_stages_project_id_sequence_unique');

            Schema::table('pm_project_stages', function (Blueprint $table) {
                $table->unique(['project_id', 'sequence']);
            });
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS pm_workflow_stages_workflow_id_sequence_unique');
            DB::statement('DROP INDEX IF EXISTS pm_workflow_stages_workflow_id_code_unique');
            DB::statement('DROP INDEX IF EXISTS pm_project_stages_project_id_sequence_unique');
        }
    }
};
