<?php

namespace App\Modules\DelayReport\Tests\Feature;

use App\Modules\Agent\Models\Agent;
use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use App\Modules\DelayReport\Services\DelayReportService;
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

    // start of STORE function

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
            'order_id' => $order->id,
        ]);

        $this->post('order-delay-report', ['order_id' => $order->id])
            ->assertStatus(500)
            ->assertJson([
                'status' => false,
                'message' => 'Delay has already been submitted for this order.',
            ]);
    }

    // end of STORE function

    // start of GET function

    /** @test */
    public function check_structure_of_assignable_delayed_orders_queues()
    {
        Vendor::factory()->create();
        Agent::factory()->create();

        DelayedOrdersQueue::factory()->create([
            'agent_id' => null,
            'status' => 'UNCHECKED',
        ]);

        DelayedOrdersQueue::factory()->create([
            'status' => 'CHECKING',
        ]);

        DelayedOrdersQueue::factory()->create([
            'status' => 'CHECKED',
        ]);

        $this->get('order-delay-report')
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'agent',
                        'order' => ['totalPrice', 'deliveryTime'],
                    ],
                ],
            ]);
    }

    // end of GET function

    // start of UPDATE function

    /** @test */
    public function delayed_order_cannot_be_assigned_to_agent_with_open_delayed_order()
    {
        Vendor::factory()->create();

        $delayedOrderQueue = DelayedOrdersQueue::factory()->create();
        $agent = Agent::factory()->create();
        DelayedOrdersQueue::factory()->create([
            'status' => 'CHECKING',
        ]);

        $this->post('order-delay-report/'.$delayedOrderQueue->id, ['agent_id' => $agent->id])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'failed',
                'message' => ['agent_id' => ['This agent has open delayed order']],
            ]);
    }

    /** @test */
    public function picked_delayed_order_queue_cannot_be_assigned()
    {
        Vendor::factory()->create();

        $agent = Agent::factory()->create();
        $delayedOrderQueue = DelayedOrdersQueue::factory()->create([
            'agent_id' => $agent->id,
        ]);
        $agent = Agent::factory()->create();

        $this->post('order-delay-report/'.$delayedOrderQueue->id, ['agent_id' => $agent->id])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'failed',
                'message' => ['delayedOrdersQueue' => ['This delayed order is picked by another agent']],
            ]);
    }

    /** @test */
    public function assign_delayed_order_to_agent_successfully()
    {
        Vendor::factory()->create();

        $delayedOrderQueue = DelayedOrdersQueue::factory()->create();
        $agent = Agent::factory()->create();

        $this->post('order-delay-report/'.$delayedOrderQueue->id, ['agent_id' => $agent->id])
            ->assertNoContent();

        $this->assertDatabaseHas('delayed_orders_queues', [
            'agent_id' => $agent->id,
        ]);
    }

    /** @test */
    public function error_occurred_when_assigning_delayed_order_to_agent_fails()
    {
        Vendor::factory()->create();

        $delayedOrderQueue = DelayedOrdersQueue::factory()->create();
        $agent = Agent::factory()->create();

        $this->mock(DelayReportService::class)->makePartial()
            ->shouldReceive('update')
            ->andReturn(false);

        $this->post('order-delay-report/'.$delayedOrderQueue->id, ['agent_id' => $agent->id])
            ->assertInternalServerError()
            ->assertJson([
                'status' => false,
                'message' => 'sth went wrong!',
            ]);
    }

    // end of UPDATE function

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
