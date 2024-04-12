<?php

namespace App\Modules\Trip\Database\Seeders;

use App\Modules\Trip\Models\Trip;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Trip::factory()->count(10)->create();
    }
}
