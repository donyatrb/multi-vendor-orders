<?php

namespace App\Modules\DelayReport\Tests\Unit;

use App\Modules\Agent\Models\Agent;
use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use App\Modules\DelayReport\Models\DelayReport;
use App\Modules\DelayReport\Services\DelayReportService;
use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
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

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
            'id' => 1,
            'order_id' => 1,
            'agent_id' => null,
            'status' => 'UNCHECKED',
        ], $getRes[0], ['id', 'order_id', 'agent_id', 'status']);

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys([
            'id' => 2,
            'order_id' => 2,
            'agent_id' => null,
            'status' => 'UNCHECKED',
        ], $getRes[1], ['id', 'order_id', 'agent_id', 'status']);
    }

    /** @test */
    public function check_sum_of_delay_reports_according_to_vendors()
    {
        [$vendor1] = $this->createDelayReports(20, 40);
        [$vendor2] = $this->createDelayReports(50, 60);
        [$vendor3] = $this->createDelayReports(100, 200);

        $service = new DelayReportService();

        $report = $service->getVendorsWeeklyDelayReports(3)->toArray()['data'];

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys(
            [
                'sum' => 300,
                'vendor_id' => $vendor3->id
            ], $report[0], ['sum', 'vendor_id']);

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys(
            [
                'sum' => 110,
                'vendor_id' => $vendor2->id
            ], $report[1], ['sum', 'vendor_id']);

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys(
            [
                'sum' => 60,
                'vendor_id' => $vendor1->id
            ], $report[2], ['sum', 'vendor_id']);
    }

    /** @test */
    public function check_cache_in_sum_of_delay_reports_according_to_vendors()
    {
        [$vendor1] = $this->createDelayReports(20, 40);
        [$vendor2] = $this->createDelayReports(50, 60);
        [$vendor3] = $this->createDelayReports(100, 200);

        $service = new DelayReportService();

        $service->getVendorsWeeklyDelayReports(3);

        $report = Cache::get('report_page_1')->toArray()['data'];

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys(
            [
                'sum' => 300,
                'vendor_id' => $vendor3->id
            ], $report[0], ['sum', 'vendor_id']);

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys(
            [
                'sum' => 110,
                'vendor_id' => $vendor2->id
            ], $report[1], ['sum', 'vendor_id']);

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys(
            [
                'sum' => 60,
                'vendor_id' => $vendor1->id
            ], $report[2], ['sum', 'vendor_id']);
    }

    private function createDelayReports(int $delayTime1, int $delayTime2): array
    {
        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create();
        DelayReport::factory()->create([
            'vendor_id' => $vendor->id,
            'order_id' => $order->id,
            'delay_time' => $delayTime1
        ]);
        $order = Order::factory()->create();
        DelayReport::factory()->create([
            'vendor_id' => $vendor->id,
            'order_id' => $order->id,
            'delay_time' => $delayTime2
        ]);

        return [$vendor, $order];
    }
}
