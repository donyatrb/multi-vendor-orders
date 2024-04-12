<?php

namespace App\Modules\DelayReport\Database\Factories;

use App\Modules\DelayReport\Models\DelayReport;
use App\Modules\Order\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<DelayReport>
 */
class DelayReportFactory extends Factory
{
    protected $model = DelayReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $order = Order::inRandomOrder()->first();
        $createdAt = now()->addMinutes(rand(100, 200));

        return [
            'vendor_id' => $order->vendor->id,
            'order_id' => $order->id,
            'delay_time' => $order->delivery_time->diffInUTCMinutes($createdAt),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
}
