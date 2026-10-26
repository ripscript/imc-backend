<?php

namespace App\Services;

use App\Models\MsRole;

class RoleService
{
    public function index()
    {
        return MsRole::all();
    }
}
