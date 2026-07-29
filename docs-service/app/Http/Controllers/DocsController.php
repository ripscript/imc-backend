<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocsController extends Controller
{
    public function landing()
    {
        $services = config('docs.services');
        return view('landing', compact('services'));
    }
}
