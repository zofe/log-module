<?php

namespace App\Modules\Log\Tests;

use App\Modules\Log\LogServiceProvider;
use App\Modules\Log\Tests\Http\Livewire\TestLogAppTable;
use App\Modules\Log\Tests\Http\Livewire\TestLogUsersTable;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Zofe\Rapyd\RapydServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        $this->afterApplicationCreated(fn () => $this->makeACleanSlate());
        $this->beforeApplicationDestroyed(fn () => $this->makeACleanSlate());

        parent::setUp();

        Livewire::component('test-log-app-table', TestLogAppTable::class);
        Livewire::component('test-log-users-table', TestLogUsersTable::class);

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    public function makeACleanSlate()
    {
        Artisan::call('view:clear');
    }

    protected function getPackageProviders($app)
    {
        return [
            RapydServiceProvider::class,
            LogServiceProvider::class,
            LivewireServiceProvider::class,
            \Spatie\Activitylog\ActivitylogServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('session.driver', 'file');
        $app['config']->set('rapyd.companies.enabled', false);
        $app['config']->set('log.database_connection', 'sqlite');
        $app['config']->set('log.table_name', 'activity_log');
        $app['config']->set('activitylog.table_name', 'activity_log');
        $app['config']->set('activitylog.database', 'sqlite');
    }
}
