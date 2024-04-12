<?php

namespace App\Modules\Auth;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend(Factory::class, function ($factory) {
            $factory->load(__DIR__.'/../Database/factories');

            return $factory;
        });
    }

    public function boot(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
