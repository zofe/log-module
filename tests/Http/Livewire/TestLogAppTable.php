<?php

namespace App\Modules\Log\Tests\Http\Livewire;

use App\Modules\Log\Livewire\LogAppTable;

class TestLogAppTable extends LogAppTable
{
    public function booted(): void
    {
        // skip auth in tests
    }
}
