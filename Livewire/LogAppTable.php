<?php

namespace App\Modules\Log\Livewire;

use App\Modules\Auth\Traits\Authorize;
use App\Modules\Log\Services\LogParser;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class LogAppTable extends Component
{
    use Authorize;
    use WithDataTable;

    public $search;
    public $level;
    public $logFiles = [];
    public $logFile;

    public ?string $aiAnalysis = null;
    public bool $analyzing = false;
    public ?string $aiError = null;

    public function booted(): void
    {
        $this->authorize('admin');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLogFile(): void
    {
        $this->resetPage();
    }

    public function updatedLevel(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->perPage  = 50;
        $parser         = new LogParser();
        $this->logFiles = $parser->availableFiles();

        if ($this->logFiles) {
            $this->logFile = end($this->logFiles);
        }
    }

    protected function getLogArray(): array
    {
        if (!$this->logFile) {
            return [];
        }

        return (new LogParser())->parse($this->logFile);
    }

    protected function getDataset()
    {
        $items = collect($this->getLogArray());

        if ($this->level) {
            $l = $this->level;
            $items = $items->filter(fn ($log) => $log['level'] === $l)->values();
        }

        if ($this->search) {
            $s = $this->search;
            $items = $items->filter(fn ($log) =>
                stristr($log['text'] ?? '', $s) ||
                stristr($log['stack'] ?? '', $s) ||
                stristr($log['context'] ?? '', $s) ||
                stristr($log['level'] ?? '', $s)
            )->values();
        }

        $page  = $this->getPage();
        $slice = $items->slice(($page - 1) * $this->perPage, $this->perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator($slice, $items->count(), $this->perPage, $page);
    }

    public function analyzeError(string $text, string $stack): void
    {
        $key = config('log.ai_key');
        if (!$key) return;

        $this->analyzing = true;
        $this->aiAnalysis = null;
        $this->aiError = null;

        try {
            $response = Http::withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => config('log.ai_model', 'claude-haiku-4-5-20251001'),
                'max_tokens' => 512,
                'messages' => [[
                    'role' => 'user',
                    'content' => "Analyze this Laravel error briefly.\n\nError: {$text}\n\nStack trace:\n" . substr($stack, 0, 3000) . "\n\nProvide: 1) Probable cause (1-2 sentences), 2) Suggested fix (specific and actionable).",
                ]],
            ]);

            if ($response->successful()) {
                $this->aiAnalysis = $response->json('content.0.text');
            } else {
                $this->aiError = 'AI error: ' . $response->json('error.message', 'Unknown error');
            }
        } catch (\Exception $e) {
            $this->aiError = 'Request failed: ' . $e->getMessage();
        }

        $this->analyzing = false;
    }

    public function render()
    {
        $items = $this->getDataset();

        return view('log::log_app_table', compact('items'))
            ->layout('log::admin');
    }
}
