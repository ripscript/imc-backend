<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiinasLoginRequest;
use App\Services\SiinasAuthService;

class SiinasAuthController extends Controller
{
    public function __construct(
        protected SiinasAuthService $siinasAuthService
    ) {}

    public function login(SiinasLoginRequest $request)
    {
        try {
            $result = $this->siinasAuthService->login(
                $request->validated('username'),
                $request->validated('password')
            );

            return response()->json($result);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
