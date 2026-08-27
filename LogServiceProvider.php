<?php

namespace App\Modules\Log;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'log');
        $this->loadRoutesFrom(__DIR__ . '/Livewire/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/Lang', 'log');

        Livewire::addNamespace('log', __DIR__ . '/Livewire');

        if (class_exists(\Zofe\Ai\AiRegistry::class)) {
            \Zofe\Ai\AiRegistry::register(new \App\Modules\Log\Ai\LogAiToolProvider());
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config.php' => config_path('log.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'log');
    }
}
