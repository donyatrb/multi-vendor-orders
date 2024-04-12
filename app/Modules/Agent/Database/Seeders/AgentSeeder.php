<?php

namespace App\Modules\Agent\Database\Seeders;

use App\Modules\Agent\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Agent::factory()->count(10)->create();
    }
}
