<?php

namespace App\Modules\DelayReport\Database\Seeders;

use App\Modules\DelayReport\Models\DelayReport;
use Illuminate\Database\Seeder;

class DelayReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DelayReport::factory()->count(10)->create();
    }
}
