<x-rpd::card>
    <x-rpd::table title="Activity Logs" :items="$items">

        <x-slot name="filters">
            <x-rpd::date col="col" model="date_from" label="From" />
            <x-rpd::date col="col" model="date_to" label="To" />
            <div class="form-check ms-2 align-self-center">
                <input wire:model.live="exclude" type="checkbox" class="form-check-input" id="exclude">
                <label class="form-check-label" for="exclude">{{ __('log::log.exclude_system_log') }}</label>
            </div>
            <x-rpd::select-list col="col" model="user" multiple :options="$users" placeholder="Author..." />
            <x-rpd::select-list col="col" model="log_name" multiple :options="$log_names" placeholder="Log name..." />
            <x-rpd::input col="col" model="search" placeholder="Search..." />
        </x-slot>

        <x-slot name="buttons">
            <x-rpd::button :route="'log.users'" color="outline-secondary" label="Reset" />
        </x-slot>

        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Subject</th>
                    <th><x-rpd::sort model="log_name" label="Log name" /></th>
                    <th>Description</th>
                    <th><x-rpd::sort model="created_at" label="Created at" /></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($items as $log)
                <tr>
                    <td class="small text-nowrap">
                        @if($log->causer)
                            {{ $log->causer->name ?? $log->causer_id }}
                        @else
                            <span class="text-muted">system</span>
                        @endif
                    </td>
                    <td class="small">
                        @if($log->subject_type)
                            <span class="text-muted">{{ class_basename($log->subject_type) }}</span>
                            @if($log->subject_id) <code>{{ $log->subject_id }}</code> @endif
                        @endif
                    </td>
                    <td class="small text-nowrap">
                        {{ __("log::title.{$log->log_name}", [], null) ?? $log->log_name }}
                    </td>
                    <td class="small" style="max-width: 320px;">
                        <div style="max-height: 160px; overflow-y: auto">
                            {!! \App\Modules\Log\Services\ActivityLogService::makeActivityLogReadable($log) !!}
                        </div>
                    </td>
                    <td class="small text-nowrap">
                        {{ $log->created_at->setTimezone($timezone)->format('d/m/Y H:i:s') }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </x-rpd::table>
</x-rpd::card>

@once
    @push('footer_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                Livewire.dispatch('userTimezone', { timezone: timezone });
            });
        </script>
    @endpush
@endonce
