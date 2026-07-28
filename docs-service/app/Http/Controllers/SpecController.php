<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class SpecController extends Controller
{
    public function show(string $service): JsonResponse
    {
        $path = storage_path("app/specs/{$service}.json");

        if (!File::exists($path)) {
            abort(404, "Spec for service '{$service}' not found.");
        }

        $json = File::get($path);

        return response()->json(json_decode($json, true), 200, [], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
