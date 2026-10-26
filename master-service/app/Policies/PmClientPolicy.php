<?php

namespace App\Policies;

use App\Models\PmClient;
use App\Models\User;

class PmClientPolicy
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

    public function view(User $user, PmClient $model): bool
    {
        return $this->isAllowed($user);
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function update(User $user, PmClient $model): bool
    {
        return $this->isAllowed($user);
    }

    public function delete(User $user, ?PmClient $model = null): bool
    {
        return $this->isAllowed($user);
    }
}
