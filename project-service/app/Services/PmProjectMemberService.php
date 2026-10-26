<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\PmProjectMember;
use App\Models\Project;

class PmProjectMemberService
{
    public function list(int $projectId)
    {
        $project = Project::findOrFail($projectId);

        return PmProjectMember::with('user')
            ->where('project_id', $project->id)
            ->orderBy('id')
            ->get();
    }

    public function add(int $projectId, int $userId, ?string $role = null, ?string $actor = null): PmProjectMember
    {
        $project = Project::findOrFail($projectId);

        $existing = PmProjectMember::withTrashed()
            ->where('project_id', $project->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing && ! $existing->trashed()) {
            throw new ValidationException(
                'Pengguna sudah menjadi anggota proyek ini.',
                ['user_id' => ["User id {$userId} sudah terdaftar pada proyek {$projectId}."]]
            );
        }

        $member = $existing ?? new PmProjectMember([
            'project_id' => $project->id,
            'user_id' => $userId,
            'created_by' => $actor,
        ]);
        $member->role = $role ?? 'member';
        $member->status = 'active';
        $member->updated_by = $actor;
        $member->deleted_at = null;
        $member->save();

        return $member->load('user');
    }

    public function remove(int $projectId, int $memberId): void
    {
        $project = Project::findOrFail($projectId);

        $member = PmProjectMember::where('project_id', $project->id)
            ->where('id', $memberId)
            ->firstOrFail();

        $referenced = (int) $project->pic_user_id === (int) $member->user_id
            || $project->stages()->where('pic_user_id', $member->user_id)->exists();

        if ($referenced) {
            throw new ValidationException(
                'Anggota masih tercatat sebagai PIC dan tidak dapat dihapus.',
                ['member' => ['Lepaskan peran PIC pengguna ini terlebih dahulu.']]
            );
        }

        $member->delete();
    }

    /**
     * Auto-sync rule (data-model): project/stage PICs are members with
     * role `pic`. Called on activation and whenever a PIC is (re)assigned.
     */
    public function syncPic(int $projectId, int $userId, ?string $actor = null): PmProjectMember
    {
        $member = PmProjectMember::withTrashed()->firstOrNew([
            'project_id' => $projectId,
            'user_id' => $userId,
        ]);

        if (! $member->exists) {
            $member->created_by = $actor;
        }
        $member->role = 'pic';
        $member->status = 'active';
        $member->updated_by = $actor;
        $member->deleted_at = null;
        $member->save();

        return $member;
    }
}
