<?php

/*
|--------------------------------------------------------------------------
| Log Module Configurations
|--------------------------------------------------------------------------
|
|
*/
return [
    'layout' => 'log::admin',
    'menu_admin' => 'log::admin_menu',
    'menu_admin_position' => 100,

    'database_connection' => config('database.default'),
    'table_name' => 'activity_log',
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),
    'delete_records_older_than_days' => 60,
    'default_log_name' => 'default',
    'default_auth_driver' => null,
    'subject_returns_soft_deleted_models' => false,
    'activity_model' => \App\Modules\Log\Models\Activity::class,



];
