<?php

namespace App\Modules\DelayReport\Tests\Unit;

use App\Modules\Agent\Models\Agent;
use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use App\Modules\DelayReport\Services\DelayReportService;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DelayReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_get_assignable_delayed_orders()
    {
        Vendor::factory()->create();

        DelayedOrdersQueue::factory()->create(
            [
                'agent_id' => null,
                'status' => 'UNCHECKED',
            ]);

        DelayedOrdersQueue::factory()->create(
            [
                'agent_id' => null,
                'status' => 'UNCHECKED',
            ]);

        Agent::factory()->create();
        DelayedOrdersQueue::factory()->create(
            [
                'status' => 'CHECKING',
            ]);

        DelayedOrdersQueue::factory()->create(
            [
                'status' => 'CHECKED',
            ]);

        $service = new DelayReportService();

        $getRes = $service->get()->toArray();

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([[
            'id' => 1,
            'order_id' => 1,
            'agent_id' => null,
            'status' => 'UNCHECKED',
        ], [
            'id' => 2,
            'order_id' => 2,
            'agent_id' => null,
            'status' => 'UNCHECKED',
        ], [
            'id' => 3,
            'order_id' => 3,
            'agent_id' => null,
            'status' => 'CHECKED',
        ]], $getRes, ['id', 'order_id', 'agent_id', 'status']);
    }
}
