<?php

namespace Database\Seeders;

use App\Modules\Agent\Database\Seeders\AgentSeeder;
use App\Modules\Auth\Models\User;
use App\Modules\DelayReport\Database\Seeders\DelayReportSeeder;
use App\Modules\Order\Database\Seeders\OrderSeeder;
use App\Modules\Trip\Database\Seeders\TripSeeder;
use App\Modules\Vendor\Database\Seeders\VendorSeeder;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            VendorSeeder::class,
            AgentSeeder::class,
            OrderSeeder::class,
            TripSeeder::class,
            DelayReportSeeder::class,
        ]);
    }
}
