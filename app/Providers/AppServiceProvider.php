<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'App\\Modules\\'.Str::between($modelName, 'Modules\\', '\\Models\\').'\\Database\\Factories\\'.class_basename($modelName).'Factory';
        });
    }

    public function boot(): void
    {
        //
    }
}
