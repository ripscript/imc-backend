<?php

namespace App\Policies;

use App\Models\LatestInformation;
use App\Models\User;

class LatestInformationPolicy
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

    public function view(User $user, LatestInformation $model): bool
    {
        return $this->isAllowed($user);
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function update(User $user, LatestInformation $model): bool
    {
        return $this->isAllowed($user);
    }

    public function delete(User $user, LatestInformation $model): bool
    {
        return $this->isAllowed($user);
    }
}
