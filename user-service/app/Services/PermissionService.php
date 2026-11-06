<?php

namespace App\Services;

use App\Exceptions\Domain\EntityNotFoundException;
use App\Exceptions\Domain\ValidationException;
use App\Models\MsPermission;
use App\Services\DTOs\PermissionDTO;
use Illuminate\Http\Request;

class PermissionService
{
    public function index(Request $request)
    {
        $query = MsPermission::query();

        if ($request->filled('group')) {
            $query->where('group', $request->query('group'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('action', 'like', $search)
                    ->orWhere('code', 'like', $search)
                    ->orWhere('label', 'like', $search)
                    ->orWhere('group', 'like', $search);
            });
        }

        $sortField = $request->query('sort_field', 'group');
        $sortDirection = $request->query('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection)->orderBy('code', 'asc');

        if ($request->boolean('all', false)) {
            return $query->get();
        }

        $perPage = (int) $request->query('limit', 20);

        return $query->paginate($perPage);
    }

    public function detail(int|string $id): MsPermission
    {
        $permission = MsPermission::find($id);
        if (! $permission) {
            throw new EntityNotFoundException('Hak Akses', (string) $id);
        }

        return $permission;
    }

    public function create(PermissionDTO $dto): MsPermission
    {
        $name = strtolower(trim((string) $dto->name));
        $action = strtolower(trim((string) $dto->action));
        $code = "{$name}.{$action}";

        if (MsPermission::where('code', $code)->exists()) {
            throw new ValidationException('Kode hak akses sudah terdaftar', [
                'code' => ["Kode '{$code}' sudah ada."],
            ]);
        }

        return MsPermission::create([
            'name' => $name,
            'action' => $action,
            'code' => $code,
            'label' => $dto->label,
            'group' => $dto->group,
            'is_system' => false,
        ]);
    }

    public function update(int|string $id, PermissionDTO $dto): MsPermission
    {
        $permission = $this->detail($id);

        $permission->update([
            'label' => $dto->label ?? $permission->label,
            'group' => $dto->group !== null ? $dto->group : $permission->group,
        ]);

        return $permission;
    }

    public function destroy(int|string $id): bool
    {
        $permission = $this->detail($id);

        if ($permission->is_system) {
            throw new ValidationException('Hak akses sistem tidak dapat dihapus', [
                'is_system' => ['Hak akses ini dilindungi sistem.'],
            ]);
        }

        return (bool) $permission->delete();
    }
}
