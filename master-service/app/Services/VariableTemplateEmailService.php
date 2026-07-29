<?php

namespace App\Services;

use App\Models\MsVariableTemplateEmail;
use Illuminate\Http\Request;

class VariableTemplateEmailService
{
    public function index(Request $request)
    {
        $query = MsVariableTemplateEmail::query();

        if ($request->filled('template_email')) {
            $query->where('template_email', $request->input('template_email'));
        }

        return $query->get();
    }
}
