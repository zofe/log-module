<?php

namespace App\Modules\Log\Tests\Feature;

use App\Modules\Log\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

class LogUsersTableTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Alice Admin', 'email' => 'alice@example.com', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Bob User',   'email' => 'bob@example.com',   'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('activity_log')->insert([
            [
                'log_name'     => 'login',
                'description'  => 'login',
                'subject_type' => null,
                'subject_id'   => null,
                'event'        => 'login',
                'causer_type'  => null,
                'causer_id'    => null,
                'properties'   => json_encode([]),
                'batch_uuid'   => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'log_name'     => 'logout',
                'description'  => 'logout',
                'subject_type' => null,
                'subject_id'   => null,
                'event'        => 'logout',
                'causer_type'  => null,
                'causer_id'    => null,
                'properties'   => json_encode([]),
                'batch_uuid'   => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }

    // The view renders the translated description via ActivityLogService::makeActivityLogReadable(),
    // not the raw DB description field. So we assert the translated strings from log::log.
    public function test_renders_activity_log_entries(): void
    {
        Livewire::test('test-log-users-table')
            ->assertSee('Has logged into the platform.')
            ->assertSee('Has logged out of the platform.');
    }

    public function test_search_filters_activity_entries(): void
    {
        // 'logout' matches log_name LIKE '%logout%'; 'login' does not contain 'logout'
        Livewire::test('test-log-users-table')
            ->set('search', 'logout')
            ->assertSee('Has logged out of the platform.')
            ->assertDontSee('Has logged into the platform.');
    }
}
