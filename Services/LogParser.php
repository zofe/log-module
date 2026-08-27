<?php

namespace App\Modules\Log\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogParser
{
    private array $levelsClasses = [
        'debug'     => 'info',
        'info'      => 'info',
        'notice'    => 'info',
        'warning'   => 'warning',
        'error'     => 'danger',
        'critical'  => 'danger',
        'alert'     => 'danger',
        'emergency' => 'danger',
        'processed' => 'info',
        'failed'    => 'warning',
    ];

    public function availableFiles(): array
    {
        $this->setDisk();

        return collect(Storage::disk('log')->files())
            ->filter(fn ($f) => Str::endsWith($f, '.log'))
            ->values()
            ->all();
    }

    public function latestFile(): ?string
    {
        $files = $this->availableFiles();
        return $files ? end($files) : null;
    }

    public function parse(string $logFile, int $maxBytes = 512_000): array
    {
        $path = storage_path('logs/' . $logFile);
        if (!file_exists($path)) {
            return [];
        }

        $content = $this->tail($path, $maxBytes);

        preg_match_all('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?\].*/', $content, $headings);

        if (empty($headings[0])) {
            return array_reverse(array_map(
                fn ($line, $key) => ['context' => '', 'level' => '', 'level_class' => '', 'date' => $key + 1, 'text' => $line, 'stack' => ''],
                explode(PHP_EOL, $content),
                array_keys(explode(PHP_EOL, $content))
            ));
        }

        $logData = preg_split('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?\].*/', $content);
        if (($logData[0] ?? '') < 1) {
            array_shift($logData);
        }

        $levels = array_keys($this->levelsClasses);
        $log    = [];

        foreach ($headings[0] as $i => $heading) {
            foreach ($levels as $level) {
                $lower = strtolower($heading);
                if (!str_contains($lower, '.' . $level) && !str_contains($lower, $level . ':')) {
                    continue;
                }
                preg_match(
                    '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?)\](?:.*?(\w+)\.|.*?)' . $level . ': (.*?)( in .*?:[0-9]+)?$/i',
                    $heading,
                    $current
                );
                if (!isset($current[4])) {
                    continue;
                }
                $stack = preg_replace("/^\n*/", '', $logData[$i] ?? '');
                $log[] = [
                    'context'     => $current[3],
                    'level'       => $level,
                    'level_class' => $this->levelsClasses[$level],
                    'date'        => $current[1],
                    'text'        => $current[4],
                    'stack'       => $stack,
                ];
                break;
            }
        }

        return array_reverse($log);
    }

    private function setDisk(): void
    {
        $disks        = config('filesystems.disks');
        $disks['log'] = ['driver' => 'local', 'root' => storage_path('logs')];
        Config::set('filesystems.disks', $disks);
    }

    private function tail(string $path, int $maxBytes): string
    {
        $size = filesize($path);
        if ($size === 0) return '';

        $fp = fopen($path, 'rb');
        if (!$fp) return '';

        if ($size <= $maxBytes) {
            $content = fread($fp, $size);
        } else {
            fseek($fp, -$maxBytes, SEEK_END);
            fgets($fp);
            $content = stream_get_contents($fp);
        }

        fclose($fp);
        return $content;
    }
}
