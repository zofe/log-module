<?php

namespace App\Modules\Log\Tests\Http\Livewire;

use App\Modules\Log\Livewire\LogUsersTable;

class TestLogUsersTable extends LogUsersTable
{
    public function booted(): void
    {
        // skip auth in tests
    }
}
