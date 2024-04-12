<?php

namespace App\Modules\Agent\Database\Factories;

use App\Modules\Agent\Models\Agent;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Agent>
 */
class AgentFactory extends Factory
{
    protected $model = Agent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::inRandomOrder()->first()->id,
            'first_name' => $this->faker->name,
            'last_name' => $this->faker->lastName,
        ];
    }
}
