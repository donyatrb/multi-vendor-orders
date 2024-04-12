<?php

namespace App\Modules\Order\Database\Factories;

use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::inRandomOrder()->first()->id,
            'items_count' => $this->faker->numberBetween(1, 10),
            'total_price' => rand(100000, 999999),
            'delivery_time' => now()->addMinutes(rand(30, 50)),
        ];
    }

    public function delayed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'delivery_time' => now()->subMinutes(10),
                'created_at' => now()->subHour(),
                'updated_at' => now()->subHour(),
            ];
        });
    }
}
