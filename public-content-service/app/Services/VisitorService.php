<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class VisitorService
{
    public function track(array $data)
    {
        $ipAddress = $data['ip'] ?? request()->ip();
        $userAgent = $data['user_agent'] ?? request()->userAgent();
        $deviceType = $this->getDeviceType($userAgent);

        $existingVisitor = DB::table('visitors')
            ->where('ip_address', $ipAddress)
            ->where('user_agent', $userAgent)
            ->where('device_type', $deviceType)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if (! $existingVisitor) {
            DB::table('visitors')->insert([
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_type' => $deviceType,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return true;
    }

    protected function getDeviceType($userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }
}
