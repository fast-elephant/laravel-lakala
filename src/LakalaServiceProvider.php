<?php

namespace FastElephant\LaravelLakala;

use Illuminate\Support\ServiceProvider;

class LakalaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/lakala.php',
            'lakala'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/lakala.php' => config_path('lakala.php'),
        ]);
    }
}
