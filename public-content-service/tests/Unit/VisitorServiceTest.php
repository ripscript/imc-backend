<?php

namespace Tests\Unit;

use App\Services\VisitorService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class VisitorServiceTest extends TestCase
{
    public function test_can_instantiate_service()
    {
        $service = app()->make(VisitorService::class);
        $this->assertInstanceOf(VisitorService::class, $service);
    }

    public function test_track_creates_new_visitor_when_not_exists()
    {
        $service = new VisitorService();

        DB::shouldReceive('table')
            ->with('visitors')
            ->once()
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('ip_address', '192.168.1.1')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('user_agent', 'Mozilla/5.0')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('device_type', 'desktop')
            ->andReturnSelf();

        DB::shouldReceive('whereDate')
            ->with('created_at', now()->toDateString())
            ->andReturnSelf();

        DB::shouldReceive('first')
            ->once()
            ->andReturn(null);

        DB::shouldReceive('insert')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['ip_address'] === '192.168.1.1'
                    && $data['user_agent'] === 'Mozilla/5.0'
                    && $data['device_type'] === 'desktop';
            }));

        $result = $service->track([
            'ip' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $this->assertTrue($result);
    }

    public function test_track_does_not_insert_when_visitor_exists()
    {
        $service = new VisitorService();

        DB::shouldReceive('table')
            ->with('visitors')
            ->once()
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('ip_address', '192.168.1.1')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('user_agent', 'Mozilla/5.0')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('device_type', 'desktop')
            ->andReturnSelf();

        DB::shouldReceive('whereDate')
            ->with('created_at', now()->toDateString())
            ->andReturnSelf();

        DB::shouldReceive('first')
            ->once()
            ->andReturn((object) ['id' => 1]);

        DB::shouldNotReceive('insert');

        $result = $service->track([
            'ip' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $this->assertTrue($result);
    }

    public function test_track_detects_mobile_device()
    {
        $service = new VisitorService();

        DB::shouldReceive('table')
            ->with('visitors')
            ->once()
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('ip_address', '10.0.0.1')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('user_agent', 'Mozilla/5.0 iPhone')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->with('device_type', 'mobile')
            ->andReturnSelf();

        DB::shouldReceive('whereDate')
            ->with('created_at', now()->toDateString())
            ->andReturnSelf();

        DB::shouldReceive('first')
            ->once()
            ->andReturn(null);

        DB::shouldReceive('insert')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['device_type'] === 'mobile';
            }));

        $result = $service->track([
            'ip' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0 iPhone',
        ]);

        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
