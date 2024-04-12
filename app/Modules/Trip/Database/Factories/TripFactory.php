<?php

namespace App\Modules\Trip\Database\Factories;

use App\Modules\Order\Models\Order;
use App\Modules\Trip\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::inRandomOrder()->first()->id,
            'status' => Arr::random(array_values(Trip::STATUSES)),
        ];
    }
}
