<?php

/*
|--------------------------------------------------------------------------
| Log Module Configurations
|--------------------------------------------------------------------------
|
|
*/
return [
    'ai_key' => env('ANTHROPIC_API_KEY'),
    'ai_model' => env('LOG_AI_MODEL', 'claude-haiku-4-5-20251001'),

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

    'visible_properties' => [
        'create_company' => [
            'business_name', 'email'
        ],
        'edit_company' => [
            'business_name','lastname','firstname','address','city','province','phone','vat',
            'streetnumber', 'zipcode', 'email'
        ],
        'remove_company' => [
            'business_name'
        ],
        'create_user' => [
            'firstname','lastname','email'
        ],
        'edit_user' => [
            'firstname','lastname','email', 'telegram_group_id', 'phone', 'smtp_host',
            'smtp_port', 'smtp_username', 'smtp_password', 'smtp_from', 'smtp_encryption',
            'smtp_to'
        ],
        'delete_user' => [
            'firstname', 'lastname'
        ],
        'edit_user_roles' => [],
        'create_role' => [
            'name', 'permissions'
        ],
        'edit_role' => [
            'name', 'permissions'
        ],
        'create_router' => [
            'name', 'customer_name'
        ],
        'edit_router' => [
            'name', 'address', 'city', 'province', 'phone', 'streetnumber', 'zipcode', 'timezone'
        ],
        'remove_router' => [
            'name', 'customer_name'
        ],
        'execute_backup' => [
           'router_id', 'backup','note'
        ],
        'restore_backup' => [
            'backup'
        ],
        'delete_backup' => [
            'backup'
        ],
        'create_router_reboot' => [
            'name', 'one_time_run', 'cron_expression', 'one_time_datetime'
        ],
        'edit_router_reboot' => [
            'name', 'one_time_run', 'cron_expression', 'one_time_datetime'
        ],
        'delete_router_reboot' => [
            'name',
        ],
        'create_dnat' => [
                'name', 'protocol', 'source_ip', 'internal_ip', 'internal_port', 'external_port', 'external_ip', 'is_dmz', 'is_enabled'
        ],
        'edit_dnat' => [
            'name', 'protocol', 'source_ip', 'internal_ip', 'internal_port', 'external_port', 'external_ip', 'is_dmz', 'is_enabled'
        ],
        'create_snat' => [
            'name', 'internal_ip', 'external_ip', 'is_enabled'
        ],
        'edit_snat' => [
            'name', 'internal_ip', 'external_ip', 'is_enabled'
        ],
        'create_bypass' => [
            'name', 'protocol', 'bypass_destination_ip', 'bypass_destination_port', 'bypass_ifName', 'is_enabled'
        ],
        'edit_bypass' => [
            'name', 'protocol', 'bypass_destination_ip', 'bypass_destination_port', 'bypass_ifName', 'is_enabled'
        ],
        'create_ztna_rule' => [
            'name', 'source_model_type', 'source_model_id', 'destination_model_type', 'destination_model_id', 'direction'
        ],
        'edit_ztna_rule' => [
            'name', 'source_model_type', 'source_model_id', 'destination_model_type', 'destination_model_id', 'direction'
        ],
        'create_ztna_object' => [
            'name', 'type', 'ip', 'port', 'protocol', 'is_internet_enabled'
        ],
        'edit_ztna_object' => [
            'name', 'type', 'ip', 'port', 'protocol', 'is_internet_enabled'
        ],
        'create_ztna_tag' => [
            'name'
        ],
        'edit_ztna_tag' => [
            'name'
        ],
        'create_router_group' => [
            'name', 'description'
        ],
        'edit_router_group' => [
            'name', 'description'
        ],
        'add_service'  => [
            'service_id'
        ],
        'create_ticket' => [
            'subject'
        ],
        'create_ticket_comment' => [
            'content',
        ],
        'wallet' => [
            'auto_recharge','purchase_coins','payment_method'
        ],
        'create_weathermap_link' => [
            'source_id', 'destination_id', 'router_ifName', 'router_interface_type'
        ],
        'edit_weathermap_link' => [
            'source_id', 'destination_id', 'router_ifName', 'router_interface_type'
        ]
    ]

];
