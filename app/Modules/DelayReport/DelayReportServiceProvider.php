<?php

namespace App\Modules\DelayReport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class DelayReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    public function boot(): void
    {
        Http::fake([
            config('services.delay_report.new_delivery_time') => Http::response($this->fakeSuccessResponse()),
        ]);
    }

    private function fakeSuccessResponse(): array
    {
        return [
            'status' => true,
            'deliveryTime' => now()->addMinutes(20)->toDateTimeString(),
        ];
    }
}
