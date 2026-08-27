@if(optional(auth()->user())->hasRoleOrPermission('admin|edit everything|can view logs'))
    @if(config('features.see_logs') || optional(auth()->user())->hasRoleOrPermission('admin|edit everything'))
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Logs</div>
        @if(optional(auth()->user())->hasRoleOrPermission('admin|edit everything'))
            <x-rpd::nav-dropdown icon="fas fa-fw fa-eye" label="Logs" active="/log/users|/log/app">
                <x-rpd::nav-link label="App Logs" route="log.app" type="collapse-item" />
                <x-rpd::nav-link label="User Logs" route="log.users" type="collapse-item" />
            </x-rpd::nav-dropdown>
        @elseif(optional(auth()->user())->hasRoleOrPermission('can view logs'))
            <x-rpd::nav-item icon="fas fa-fw fa-eye" label="Logs" route="log.users"  />
        @endif
    @endif
@endif
