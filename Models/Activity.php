<?php

namespace App\Modules\Log\Models;



use Illuminate\Support\Arr;

class Activity extends \Spatie\Activitylog\Models\Activity
{

    public function getSafePropertiesAttribute()
    {
        if($this->log_name && is_array(config('log.visible_properties.'.$this->log_name))) {
            return Arr::only($this->properties->toArray(), config('log.visible_properties.'.$this->log_name));
        }
        return [];
    }
}
