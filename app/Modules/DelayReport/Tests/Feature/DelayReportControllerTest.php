<?php

namespace App\Modules\DelayReport\Tests\Feature;

use App\Modules\Agent\Models\Agent;
use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use App\Modules\Order\Models\Order;
use App\Modules\Trip\Models\Trip;
use App\Modules\Vendor\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DelayReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.delay_report.new_delivery_time' => 'http://fake.url',
        ]);
    }

    /** @test */
    public function error_occurred_when_order_does_not_exist()
    {
        $this->post('order-delay-report', [
            'order_id' => 1,
        ])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'failed',
                'message' => [
                    'order_id' => ['The selected order id is invalid.',
                    ],
                ],
            ]);
    }

    /** @test */
    public function error_occurred_when_delivery_time_has_not_been_touched()
    {
        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create([
            'vendor_id' => $vendor->id,
        ]);

        $this->post('order-delay-report', [
            'order_id' => $order->id,
        ])
            ->assertStatus(500)
            ->assertJson([
                'status' => false,
                'message' => 'delivery time has not been yet touched!',
            ]);
    }

    /** @test */
    public function delay_report_is_being_created_if_order_id_exists()
    {
        $now = now();

        Carbon::setTestNow(now()->subHour());

        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create([
            'vendor_id' => $vendor->id,
        ]);

        Carbon::setTestNow($now);

        $this->post('order-delay-report', [
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseCount('delay_reports', 1);
        $this->assertDatabaseHas('delay_reports', [
            'order_id' => $order->id,
            'vendor_id' => $order->vendor_id,
            'delay_time' => $order->delivery_time->diffInUTCMinutes(now()),
        ]);
    }

    /** @test */
    public function new_delivery_time_has_been_set_with_success_response_when_trip_is_non_delivered()
    {
        $now = now();

        Carbon::setTestNow(now()->subHour());

        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create([
            'vendor_id' => $vendor->id,
        ]);

        $tripStatuses = Trip::STATUSES;
        unset($tripStatuses['delivered']);

        Trip::factory()->create([
            'order_id' => $order->id,
            'status' => fake()->randomElement($tripStatuses),
        ]);

        Carbon::setTestNow($now);

        $deliveryTime = now()->addMinutes(20)->toDateTimeString();

        Http::fake([
            'http://fake.url' => Http::response($this->fakeSuccessResponse($deliveryTime)),
        ]);

        $this->post('order-delay-report', [
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'vendor_id' => $vendor->id,
            'delivery_time' => $deliveryTime,
        ]);
    }

    /** @test */
    public function error_occurred_with_failure_response_when_trip_is_non_delivered()
    {
        $now = now();

        Carbon::setTestNow(now()->subHour());

        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create([
            'vendor_id' => $vendor->id,
        ]);

        $tripStatuses = Trip::STATUSES;
        unset($tripStatuses['delivered']);

        Trip::factory()->create([
            'order_id' => $order->id,
            'status' => fake()->randomElement($tripStatuses),
        ]);

        Carbon::setTestNow($now);

        Http::fake([
            'http://fake.url' => Http::response($this->fakeFailureResponse()),
        ]);

        $this->post('order-delay-report', ['order_id' => $order->id])
            ->assertStatus(500)
            ->assertJson([
                'status' => false,
                'message' => 'An error occurred during order delay submission!',
            ]);
    }

    /** @test */
    public function delayed_order_queue_created_when_trip_does_not_exist()
    {
        $now = now();

        Carbon::setTestNow(now()->subHour());

        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create([
            'vendor_id' => $vendor->id,
        ]);

        Carbon::setTestNow($now);

        $this->post('order-delay-report', ['order_id' => $order->id])
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Order delay has been successfully submitted!',
            ]);

        $this->assertDatabaseHas('delayed_orders_queues', [
            'order_id' => $order->id,
            'agent_id' => null,
            'status' => 'UNCHECKED',
        ]);
    }

    /**
     * @test
     */
    public function avoid_delayed_order_creation_if_non_checked_one_exists()
    {
        $now = now();

        Carbon::setTestNow(now()->subHour());

        $vendor = Vendor::factory()->create();

        $order = Order::factory()->create([
            'vendor_id' => $vendor->id,
        ]);

        Agent::factory()->create();

        Carbon::setTestNow($now);

        DelayedOrdersQueue::factory()->create([
            'order_id' => $order->id
        ]);

        $this->post('order-delay-report', ['order_id' => $order->id])
            ->assertStatus(500)
            ->assertJson([
                'status' => false,
                'message' => 'Delay has already been submitted for this order.',
            ]);
    }

    private function fakeSuccessResponse(string $deliveryTime): array
    {
        return [
            'status' => true,
            'deliveryTime' => $deliveryTime,
        ];
    }

    private function fakeFailureResponse(): array
    {
        return [
            'status' => false,
            'message' => 'sth',
        ];
    }
}
