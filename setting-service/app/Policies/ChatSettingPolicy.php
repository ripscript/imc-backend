<?php

namespace App\Policies;

use App\Models\ChatSetting;
use App\Models\User;

class ChatSettingPolicy
{
    protected const ALLOWED_ROLES = ['super_admin', 'admin_pusat', 'admin_pustik', 'admin_satker', 'pimpinan'];

    protected function isAllowed(User $user): bool
    {
        return in_array($user->role?->slug ?? $user->role, self::ALLOWED_ROLES);
    }

    public function viewAny(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function view(User $user, ChatSetting $model): bool
    {
        return $this->isAllowed($user);
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function update(User $user, ChatSetting $model): bool
    {
        return $this->isAllowed($user);
    }

    public function delete(User $user, ChatSetting $model): bool
    {
        return $this->isAllowed($user);
    }
}
