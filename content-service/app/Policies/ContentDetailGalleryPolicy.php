<?php

namespace App\Policies;

use App\Models\ContentDetailGallery;
use App\Models\User;

class ContentDetailGalleryPolicy
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

    public function view(User $user, ContentDetailGallery $model): bool
    {
        return $this->isAllowed($user);
    }

    public function create(User $user): bool
    {
        return $this->isAllowed($user);
    }

    public function update(User $user, ContentDetailGallery $model): bool
    {
        return $this->isAllowed($user);
    }

    public function delete(User $user, ContentDetailGallery $model): bool
    {
        return $this->isAllowed($user);
    }
}
