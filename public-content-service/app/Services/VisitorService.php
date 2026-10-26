<?php

namespace App\Services;

use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

class VisitorService
{
    public function create(array $data): void
    {
        Visitor::create([
            'ip_address' => $data['ip_address'],
            'slug' => $data['slug'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'device_type' => $data['device_type'] ?? null,
        ]);
    }

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
