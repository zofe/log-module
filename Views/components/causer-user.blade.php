@props([
'log' => null,
])
@php
    /** @var $log \App\Modules\Log\Models\Activity */

    switch ($log->subject_type) {
        case 'App\Models\User':
            $label = 'User';
            $name = optional($log->subject)->fullName;
            break;
        default:
            $label = basename($log->subject_type);
            $name = optional($log->subject)->name ?: '-';
            break;
    }

@endphp

<span class="text-nowrap">
    <strong>{{ $label }}</strong>: {{ $name }}
</span>
