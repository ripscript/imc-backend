<?php

namespace App\Services;

use App\Models\MsLayout;
use Illuminate\Http\Request;

class LayoutService
{
    public function index(Request $request)
    {
        $query = MsLayout::select('id', 'title');

        if ($request->filled('is_custom')) {
            $query->where('is_custom', $request->input('is_custom'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        return $query->get();
    }
}
