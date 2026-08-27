<div x-data="{ errorText: '', errorStack: '' }">
<x-rpd::card>
    <x-rpd::table
        title="App Logs"
        :items="$items"
    >
        <x-slot name="filters">
            <x-rpd::select col="col" lazy model="logFile" :options="$logFiles" placeholder="log file..." addempty />
            <x-rpd::select col="col-auto" lazy model="level" :options="['error','critical','alert','emergency','warning','notice','info','debug','processed','failed']" placeholder="level..." addempty />
            <x-rpd::input col="col" debounce="350" model="search" placeholder="search..." />
        </x-slot>

        <x-slot name="buttons">
            <a href="{{ route_lang('log.app') }}" class="btn btn-outline-dark">reset</a>
        </x-slot>

        <div class="modal fade" id="stackModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Error Stack</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <pre class="small" id="stackContent" style="max-height:50vh;overflow-y:auto;white-space:pre-wrap;"></pre>

                        @if($aiAnalysis)
                        <div class="alert alert-info mt-3 mb-0">
                            <strong><i class="fas fa-robot me-1"></i> AI Analysis</strong>
                            <div class="mt-2 small" style="white-space: pre-wrap;">{{ $aiAnalysis }}</div>
                        </div>
                        @endif

                        @if($aiError)
                        <div class="alert alert-danger mt-3 mb-0 small">{{ $aiError }}</div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if(config('log.ai_key'))
                        <button class="btn btn-outline-primary btn-sm"
                                :disabled="$wire.analyzing"
                                x-on:click="$wire.analyzeError(errorText, errorStack)">
                            <span x-show="!$wire.analyzing"><i class="fas fa-robot me-1"></i>Analyze with AI</span>
                            <span x-show="$wire.analyzing" x-cloak>Analyzing...</span>
                        </button>
                        @endif
                        <button class="btn btn-outline-secondary btn-sm"
                            x-on:click="navigator.clipboard.writeText(document.getElementById('stackContent').textContent).then(() => { $el.textContent = 'Copied!'; setTimeout(() => $el.textContent = 'Copy for AI', 1500); })">
                            Copy for AI
                        </button>
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-sm">
            <thead>
            <tr>
                <th>Level</th>
                <th>Context</th>
                <th>Date</th>
                <th>Content</th>
                <th>Stack</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($items as $log)
                <tr>
                    <td class="text-nowrap">
                        <span class="badge bg-{{ $log['level_class'] }}">{{ $log['level'] }}</span>
                    </td>
                    <td>
                        {{ $log['context'] }}
                    </td>
                    <td class="text-nowrap small">
                        {{ \Carbon\Carbon::parse($log['date'])->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="small">
                        {{ $log['text'] }}
                    </td>
                    <td>
                        @if($log['stack'])
                            <a href="#"
                               x-on:click.prevent="
                                   errorText = $el.dataset.text;
                                   errorStack = $el.dataset.stack;
                                   $wire.aiAnalysis = null;
                                   $wire.aiError = null;
                                   document.getElementById('stackContent').textContent = errorText + '\n\n' + errorStack;
                                   new bootstrap.Modal('#stackModal').show();
                               "
                               data-text="{{ $log['text'] }}"
                               data-stack="{{ $log['stack'] }}">
                                <i class="fas fa-list"></i>
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    </x-rpd::table>
</x-rpd::card>
</div>
