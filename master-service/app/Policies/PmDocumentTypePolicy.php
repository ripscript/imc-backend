<?php

namespace App\Policies;

use App\Models\PmDocumentType;
use App\Models\User;

class PmDocumentTypePolicy
{
    protected const ALLOWED_ROLES = ['super_admin', 'admin_pusat', 'admin_pustik'];

    protected function isAllowed(User $user): bool
    {
        return in_array($user->role?->code ?? $user->role, self::ALLOWED_ROLES);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function view(User $user, PmDocumentType $model): bool
    {
        return $this->isAllowed($user);
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function update(User $user, PmDocumentType $model): bool
    {
        return $this->isAllowed($user);
    }

    public function delete(User $user, ?PmDocumentType $model = null): bool
    {
        return $this->isAllowed($user);
    }
}
