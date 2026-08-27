<?php

namespace Zofe\Log\Traits;


use Zofe\Log\LogOptions;

trait LogsActivity {

    use \Spatie\Activitylog\Traits\LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
        // Chain fluent methods for configuration options
    }
}
