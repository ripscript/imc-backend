<?php

namespace App\Policies;

use App\Models\Pemohon;
use App\Models\User;

class PemohonPolicy
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

    public function view(User $user, Pemohon $pemohon): bool
    {
        return $this->isAllowed($user);
    }

    public function create(User $user): bool
    {
        // Pemohon registration is public via pemohon/auth/register, not admin-created
        return false;
    }

    public function update(User $user, Pemohon $pemohon): bool
    {
        return $this->isAllowed($user);
    }

    public function delete(User $user, Pemohon $pemohon): bool
    {
        return $this->isAllowed($user);
    }
}
