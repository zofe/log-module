<x-rpd::card>
    <x-rpd::table
        title="User Logs"
        :items="$items"
    >
        <x-slot name="filters">
            <x-rpd::input col="col" debounce="350" model="search"  placeholder="search..." />
        </x-slot>

        <x-slot name="buttons">
            <a href="{{ route('log.users') }}" class="btn btn-outline-dark">reset</a>
        </x-slot>

        <table class="table ">
            <thead>
            <tr>
                <th>
                    author
                </th>
                <th>
                    subject
                </th>
                <th>
                    <x-rpd::sort model="log_name" label="log name" />
                </th>
                <th>
                    <x-rpd::sort model="description" label="description" />
                </th>
                <th class="text-nowrap">
                    Data
                </th>
                <th class="d-none d-lg-table-cell">
                    <x-rpd::sort model="created_at" label="created_at" />
                </th>

            </tr>
            </thead>
            <tbody>
            @php /** @var $log \App\Modules\Log\Models\Activity */ @endphp
            @foreach ($items as $log)
                <tr>
                    <td>
                        <x-log::causer-user :log="$log"></x-log::causer-user>
                    </td>
                    <td style="width: 200px">
                        <x-log::logged-object :log="$log"></x-log::logged-object>
                    </td>
                    <td class="text-nowrap small">
                        {{ $log->log_name }}
                    </td>
                    <td class="text-gray-900">{{ $log->description }}</td>
                    <td style="max-width: 250px;" class="small">
                        <div style="max-height: 200px; overflow-y: auto">
                            {{ count($log->properties) ? $log->properties : '-' }}
                        </div>
                    </td>
                    <td class="text-nowrap small">
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>



{{--        <table class="table">--}}
{{--            <thead>--}}
{{--            <tr>--}}
{{--                <th>--}}
{{--                    <x-rpd::sort model="id" label="id" />--}}
{{--                </th>--}}
{{--                <th>name</th>--}}
{{--                <th>email</th>--}}
{{--                <th>roles</th>--}}
{{--            </tr>--}}
{{--            </thead>--}}
{{--            <tbody>--}}
{{--            @foreach ($items as $user)--}}
{{--            <tr>--}}
{{--                <td>--}}
{{--                    <a href="{{ route('auth.users.view',$user->id) }}">{{ $user->id }}</a>--}}
{{--                </td>--}}
{{--                <td>{{ $user->name }}</td>--}}
{{--                <td>{{ $user->email }}</td>--}}
{{--                <td>{{ optional(optional($user->roles)->pluck('name'))->join(',') }}</td>--}}
{{--            </tr>--}}
{{--            @endforeach--}}
{{--            </tbody>--}}
{{--        </table>--}}

    </x-rpd::table>
</x-rpd::card>

