<?php

namespace App\Modules\Order\Database\Seeders;

use App\Modules\Order\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory()->count(10)->create();
    }
}
