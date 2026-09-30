<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PmProjectManagementSeeder extends Seeder
{
    /**
     * Seed default project-management master data.
     *
     * PM master data starts empty (no pm_* seeders existed); the creation
     * wizard needs at least one workflow, document types, and sample
     * client/partner/category rows. All inserts are idempotent.
     */
    public function run(): void
    {
        $now = now()->toDateTimeString();

        $clientId = $this->firstOrCreate('pm_clients', ['identifier' => 'CL-0001'], [
            'name' => 'PT Indofest Mentari',
            'status' => 'active',
            'created_by' => 'seeder',
            'updated_by' => 'seeder',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->firstOrCreate('pm_partners', ['identifier' => 'PR-0001'], [
            'name' => 'Mitra Manufaktur Utama',
            'status' => 'active',
            'created_by' => 'seeder',
            'updated_by' => 'seeder',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->firstOrCreate('pm_project_categories', ['code' => 'PC-0001'], [
            'name' => 'Manufacturing',
            'status' => 'active',
            'created_by' => 'seeder',
            'updated_by' => 'seeder',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['DT-0001', 'Production Plan', 'pdf,doc,docx', 10],
            ['DT-0002', 'Work Order', 'pdf', 10],
            ['DT-0003', 'Production Report', 'pdf,doc,docx,xls,xlsx', 10],
            ['DT-0004', 'Supporting Document', 'pdf,doc,docx,jpg,jpeg,png', 5],
        ] as [$code, $name, $extensions, $maxSize]) {
            $this->firstOrCreate('pm_document_types', ['code' => $code], [
                'name' => $name,
                'allowed_extension' => $extensions,
                'max_size_mb' => $maxSize,
                'status' => 'active',
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $workflowId = $this->firstOrCreate('pm_workflows', ['code' => 'WF-0001'], [
            'name' => 'ETO Manufacturing',
            'version' => 1,
            'status' => 'active',
            'created_by' => 'seeder',
            'updated_by' => 'seeder',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sequence = 1;
        $blueprints = $this->blueprints();
        foreach (['Requirement', 'Feasibility', 'Proposal', 'Contract', 'Kick-off', 'Design', 'Prototype', 'Manufacturing', 'Quality Testing', 'Delivery', 'Closure'] as $stageName) {
            $blueprint = $blueprints[$stageName] ?? [];
            $stageId = $this->firstOrCreate('pm_workflow_stages', ['workflow_id' => $workflowId, 'sequence' => $sequence], [
                'code' => 'WS-'.$workflowId.'-'.$sequence,
                'name' => $stageName,
                'description' => $blueprint['description'] ?? null,
                'is_required' => true,
                'allow_skip' => false,
                'allow_repeat' => false,
                'requires_approval' => $blueprint['requires_approval'] ?? false,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->seedBlueprintChildren($stageId, $blueprint, $now);
            $sequence++;
        }
    }

    /**
     * Blueprint content per template stage (mirrors the S5 mock data).
     * Tasks/deliverables carry no due dates and no people — those are
     * always project-specific.
     */
    private function blueprints(): array
    {
        return [
            'Requirement' => [
                'description' => 'Pengumpulan dan analisis kebutuhan proyek.',
                'tasks' => [
                    ['Kumpulkan kebutuhan', 'Wawancara dan observasi kebutuhan pengguna.'],
                    ['Analisis kelayakan awal', null],
                ],
                'deliverables' => [
                    ['Dokumen Requirement', 'DT-0004', true],
                ],
            ],
            'Contract' => [
                'description' => 'Penyusunan dan penandatanganan kontrak kerja.',
                'requires_approval' => true,
                'deliverables' => [
                    ['Kontrak Kerja', 'DT-0004', true],
                ],
            ],
            'Design' => [
                'description' => 'Perancangan teknis dan gambar kerja.',
                'tasks' => [
                    ['Buat gambar teknik', null],
                    ['Review desain', null],
                ],
                'deliverables' => [
                    ['Production Plan', 'DT-0001', true],
                ],
            ],
            'Manufacturing' => [
                'description' => 'Proses produksi dan perakitan.',
                'tasks' => [
                    ['Persiapan produksi', 'Siapkan area dan material.'],
                    ['Setup mesin', null],
                    ['Produksi batch 1', null],
                ],
                'deliverables' => [
                    ['Production Plan', 'DT-0001', true],
                    ['Work Order', 'DT-0002', true],
                    ['Production Report', 'DT-0003', true],
                ],
            ],
            'Quality Testing' => [
                'description' => 'Pengujian mutu hasil produksi.',
                'tasks' => [
                    ['Uji fungsi', null],
                    ['Uji mutu', null],
                ],
                'deliverables' => [
                    ['Quality Report', 'DT-0003', true],
                ],
            ],
            'Delivery' => [
                'description' => 'Serah terima hasil pekerjaan.',
                'requires_approval' => true,
                'deliverables' => [
                    ['Berita Acara Serah Terima', 'DT-0004', true],
                ],
            ],
        ];
    }

    private function seedBlueprintChildren(int $stageId, array $blueprint, string $now): void
    {
        $sequence = 1;
        foreach ($blueprint['tasks'] ?? [] as [$title, $description]) {
            $exists = DB::table('pm_workflow_stage_tasks')
                ->where('workflow_stage_id', $stageId)
                ->where('title', $title)
                ->exists();

            if (! $exists) {
                DB::table('pm_workflow_stage_tasks')->insert([
                    'workflow_stage_id' => $stageId,
                    'title' => $title,
                    'description' => $description,
                    'priority' => 'medium',
                    'sequence' => $sequence,
                    'created_by' => 'seeder',
                    'updated_by' => 'seeder',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $sequence++;
        }

        $sequence = 1;
        foreach ($blueprint['deliverables'] ?? [] as [$name, $typeCode, $required]) {
            $exists = DB::table('pm_workflow_stage_deliverables')
                ->where('workflow_stage_id', $stageId)
                ->where('name', $name)
                ->exists();

            if (! $exists) {
                DB::table('pm_workflow_stage_deliverables')->insert([
                    'workflow_stage_id' => $stageId,
                    'document_type_id' => $typeCode ? DB::table('pm_document_types')->where('code', $typeCode)->value('id') : null,
                    'name' => $name,
                    'is_required' => $required,
                    'sequence' => $sequence,
                    'created_by' => 'seeder',
                    'updated_by' => 'seeder',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $sequence++;
        }
    }

    private function firstOrCreate(string $table, array $lookup, array $values): int
    {
        $existing = DB::table($table)->where($lookup)->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table($table)->insertGetId(array_merge($lookup, $values));
    }
}
