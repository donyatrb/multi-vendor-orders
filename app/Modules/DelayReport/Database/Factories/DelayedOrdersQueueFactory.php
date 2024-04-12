<?php

namespace App\Modules\DelayReport\Database\Factories;

use App\Modules\Agent\Models\Agent;
use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use App\Modules\Order\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<DelayedOrdersQueue>
 */
class DelayedOrdersQueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->create()->id,
            'agent_id' => Agent::inRandomOrder()->first()->id,
            'status' => $this->faker->randomElement(array_values(DelayedOrdersQueue::STATUSES)),
        ];
    }
}
