<?php

namespace App\Modules\DelayReport;

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
        //
    }
}
