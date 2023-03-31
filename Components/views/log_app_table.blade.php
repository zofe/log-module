<x-rpd::card>
    <x-rpd::table
        title="App Logs"
        :items="$items"
    >
        <x-slot name="filters">
            <x-rpd::select col="col" lazy model="logFile" :options="$logFiles" placeholder="log file..." addempty />
            <x-rpd::input col="col" debounce="350" model="search"  placeholder="search..." />
        </x-slot>

        <x-slot name="buttons">
            <a href="{{ route('log.users') }}" class="btn btn-outline-dark">reset</a>
        </x-slot>

        <table class="table table-sm">
            <thead>
            <tr>
                <th>
                    Level
                </th>
                <th>
                    Context
                </th>
                <th>
                    Date
                </th>
                <th>
                    Content
                </th>


            </tr>
            </thead>
            <tbody>

            @foreach ($items as $log)
                <tr>
                    <td>
                        {{ $log['level'] }}

                    </td>
                    <td>
                        {{ $log['context'] }}
                    </td>
                    <td class="text-nowrap small">
                        {{ \Carbon\Carbon::parse($log['date'])->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="small">

                        {{ $log['text'] }}
{{--                        {{ $log['stack'] }}--}}
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

