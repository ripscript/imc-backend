<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    protected const ALLOWED_ROLES = ['super_admin', 'admin_pusat', 'admin_pustik'];

    protected function isAllowed(User $user): bool
    {
        return in_array($user->role?->code, self::ALLOWED_ROLES, true);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->isAllowed($user);
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->isAllowed($user);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->isAllowed($user);
    }
}
