<?php


use Illuminate\Database\Eloquent\Model;

if (!function_exists('log_activity')) {
    function log_activity($logName, Model $object = null, array $properties = [], string $message = null, $user_id = null)
    {
        try {
            if($user_id){
                $causer = \App\Models\User::find($user_id);
            }else{
                $causer = (auth()->check()) ? auth()->user() : \App\Models\User::find(1);
            }

            if(!$message) {
                $message = __('activity.'.$logName);
                if ($message == 'activity.'.$logName) {
                    $message = $logName;
                }
            }

            if(in_array($logName, ['execute_scheduled_backup', 'delete_backup@scheduled'])){
                $causer = null;
            }

            $activity = activity($logName)
                ->causedBy($causer)
                ->by($causer);

            if($object) {
                $activity = $activity->performedOn($object);

                if(count($properties)<1) {
                    $props = $object->wasRecentlyCreated ? $object->toArray() : $object->getChanges();
                    if ($object->id) {
                        $props = array_merge(['id' => $object->id], $props);
                    }
                    $activity = $activity->withProperties($props);
                }
            }

            if(count($properties)) {
                $activity = $activity->withProperties($properties);
            }

            $activity->log($message);

        } catch (\Exception $e) {

        }

    }
}



