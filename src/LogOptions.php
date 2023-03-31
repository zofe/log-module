<?php

namespace Zofe\Log;


class LogOptions extends \Spatie\Activitylog\LogOptions
{
    public static function defaults(): self
    {
        return new static();
    }
}
