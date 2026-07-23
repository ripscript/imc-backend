<?php

namespace App\Policies;

use App\Models\QuickLink;
use App\Models\User;

class QuickLinkPolicy
{
    protected const ALLOWED_ROLES = ['super_admin', 'admin_pusat', 'admin_pustik'];

    protected function isAllowed(User $user): bool
    {
        return in_array($user->role?->slug ?? $user->role, self::ALLOWED_ROLES);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function view(User $user, QuickLink $model): bool
    {
        return $this->isAllowed($user);
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function update(User $user, QuickLink $model): bool
    {
        return $this->isAllowed($user);
    }

    public function delete(User $user, QuickLink $model): bool
    {
        return $this->isAllowed($user);
    }
}
