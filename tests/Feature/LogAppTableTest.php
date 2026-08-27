<?php

namespace App\Modules\Log\Tests\Feature;

use App\Modules\Log\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Livewire\Livewire;

class LogAppTableTest extends TestCase
{
    use DatabaseMigrations;

    private string $fakeLogContent = '[2026-08-23 10:00:00] production.ERROR: Something exploded in testing {"exception":"RuntimeException"}
[2026-08-23 10:01:00] production.WARNING: Low disk space warning []
[2026-08-23 10:02:00] production.INFO: Scheduled job processed []';

    protected function setUp(): void
    {
        parent::setUp();

        // setDisk() always points to storage_path('logs'), so we write there
        $logPath = storage_path('logs');
        @mkdir($logPath, 0777, true);
        file_put_contents($logPath . '/laravel-2026-08-23.log', $this->fakeLogContent);
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('logs/laravel-2026-08-23.log'));
        parent::tearDown();
    }

    public function test_renders_log_file_entries(): void
    {
        Livewire::test('test-log-app-table')
            ->assertSee('App Logs')
            ->assertSee('Something exploded in testing')
            ->assertSee('Low disk space warning');
    }

    public function test_search_filters_entries(): void
    {
        Livewire::test('test-log-app-table')
            ->set('search', 'disk space')
            ->assertSee('Low disk space warning')
            ->assertDontSee('Something exploded in testing');
    }
}
