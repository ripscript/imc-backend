<?php

namespace App\Services;

use App\Exceptions\Domain\EntityNotFoundException;
use App\Exceptions\Domain\ValidationException;
use App\Models\MsRole;
use App\Services\DTOs\RoleDTO;
use Illuminate\Http\Request;

class RoleService
{
    public function index(Request $request)
    {
        $query = MsRole::with(['permissions'])->withCount('permissions');

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('code', 'like', $search);
            });
        }

        $sortField = $request->query('sort_field', 'id');
        $sortDirection = $request->query('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        if ($request->boolean('all', false) || ! $request->has('limit')) {
            return $query->get();
        }

        $perPage = (int) $request->query('limit', 20);

        return $query->paginate($perPage);
    }

    public function detail(int|string $id): MsRole
    {
        $role = MsRole::with('permissions')->find($id);
        if (! $role) {
            throw new EntityNotFoundException('Peran (Role)', (string) $id);
        }

        return $role;
    }

    public function create(RoleDTO $dto): MsRole
    {
        $role = MsRole::create([
            'name' => $dto->name,
            'code' => $dto->code,
            'is_active' => $dto->is_active ?? true,
            'is_system' => false,
            'created_by' => $dto->created_by,
            'updated_by' => $dto->updated_by,
        ]);

        if (! empty($dto->permission_ids)) {
            $role->permissions()->sync($dto->permission_ids);
        }

        return $role->load('permissions');
    }

    public function update(int|string $id, RoleDTO $dto): MsRole
    {
        $role = $this->detail($id);

        $updateData = [
            'name' => $dto->name ?? $role->name,
            'updated_by' => $dto->updated_by,
        ];

        if ($dto->is_active !== null) {
            $updateData['is_active'] = $dto->is_active;
        }

        $role->update($updateData);

        if ($dto->permission_ids !== null) {
            $role->permissions()->sync($dto->permission_ids);
        }

        return $role->fresh('permissions');
    }

    public function destroy(int|string $id): bool
    {
        $role = $this->detail($id);

        if ($role->is_system) {
            throw new ValidationException('Peran sistem tidak dapat dihapus', [
                'is_system' => ['Peran ini dilindungi oleh sistem.'],
            ]);
        }

        $activeUsersCount = $role->users()->count();
        if ($activeUsersCount > 0) {
            throw new ValidationException("Peran tidak dapat dihapus karena masih digunakan oleh {$activeUsersCount} pengguna", [
                'users' => ["Masih ada {$activeUsersCount} pengguna aktif yang memiliki peran ini."],
            ]);
        }

        return (bool) $role->delete();
    }
}
