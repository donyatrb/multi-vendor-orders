<?php

namespace App\Modules\Vendor\Database\Seeders;

use App\Modules\Vendor\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Vendor::factory()->count(3)->create();
    }
}
