<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmDocument;
use App\Models\PmDocumentType;
use App\Services\DTOs\PmDocumentDTO;
use App\Support\ProjectAttachments;

class PmDocumentService
{
    use ResolvesProjectStage;

    public function list(int $projectId, int $stageId)
    {
        $stage = $this->resolveStage($projectId, $stageId);

        return PmDocument::with('uploader')
            ->where('project_stage_id', $stage->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->get();
    }

    /**
     * Store a document reference (file itself is uploaded via file-service).
     * Re-uploading the same file_name supersedes the previous active row.
     */
    public function create(int $projectId, int $stageId, PmDocumentDTO $dto, ?int $uploaderId = null): PmDocument
    {
        $stage = $this->resolveStage($projectId, $stageId);

        $this->validateAgainstType($dto);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($stage, $dto, $uploaderId) {
            $previous = PmDocument::where('project_stage_id', $stage->id)
                ->where('file_name', $dto->file_name)
                ->where('status', 'active')
                ->orderByDesc('version')
                ->first();

            $version = $previous ? $previous->version + 1 : 1;

            if ($previous) {
                $previous->status = 'superseded';
                $previous->updated_by = $dto->updated_by;
                $previous->save();
            }

            return PmDocument::create([
                'project_id' => $stage->project_id,
                'project_stage_id' => $stage->id,
                'document_type_id' => $dto->document_type_id,
                'file_name' => $dto->file_name,
                'file_path' => $dto->file_path,
                'file_size_kb' => $dto->file_size_kb,
                'version' => $version,
                'status' => 'active',
                'uploaded_by' => $uploaderId,
                'uploaded_at' => now(),
                'created_by' => $dto->created_by,
                'updated_by' => $dto->updated_by,
            ])->load('uploader');
        });
    }

    public function delete(int $projectId, int $stageId, int $documentId): void
    {
        $stage = $this->resolveStage($projectId, $stageId);

        PmDocument::where('project_stage_id', $stage->id)
            ->where('id', $documentId)
            ->firstOrFail()
            ->delete();
    }

    private function validateAgainstType(PmDocumentDTO $dto): void
    {
        $type = $dto->document_type_id ? PmDocumentType::find($dto->document_type_id) : null;

        $allowed = $type && $type->allowed_extension
            ? array_map(fn ($ext) => ltrim(strtolower(trim($ext)), '.'), explode(',', $type->allowed_extension))
            : array_map('strtolower', ProjectAttachments::ALLOWED_EXTENSIONS);

        $extension = strtolower(pathinfo($dto->file_name ?? '', PATHINFO_EXTENSION));

        if (! in_array($extension, $allowed, true)) {
            throw new ValidationException(
                'Ekstensi dokumen tidak diizinkan.',
                ['file_name' => ["Ekstensi .{$extension} tidak diizinkan. Diizinkan: ".implode(', ', $allowed).'.']]
            );
        }

        $maxKb = ($type?->max_size_mb ?? 5) * 1024;

        if ($dto->file_size_kb !== null && (int) $dto->file_size_kb > $maxKb) {
            throw new ValidationException(
                'Ukuran dokumen melebihi batas.',
                ['file_size_kb' => ["Ukuran dokumen maksimal {$maxKb} KB."]]
            );
        }
    }
}
