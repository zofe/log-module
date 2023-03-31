<?php

namespace App\Modules\Log\Components;



use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;


use Zofe\Auth\Traits\Authorize;
use Zofe\Rapyd\Traits\WithDataTable;

class LogAppTable extends Component
{
    use Authorize;
    use WithDataTable;
    public $search;
    public $type;
    public $logFiles = [];
    public $logFile;

    private $levels_imgs = [
        'debug' => 'info-circle',
        'info' => 'info-circle',
        'notice' => 'info-circle',
        'warning' => 'exclamation-triangle',
        'error' => 'exclamation-triangle',
        'critical' => 'exclamation-triangle',
        'alert' => 'exclamation-triangle',
        'emergency' => 'exclamation-triangle',
        'processed' => 'info-circle',
        'failed' => 'exclamation-triangle'
    ];

    private $levels_classes = [
        'debug' => 'info',
        'info' => 'info',
        'notice' => 'info',
        'warning' => 'warning',
        'error' => 'danger',
        'critical' => 'danger',
        'alert' => 'danger',
        'emergency' => 'danger',
        'processed' => 'info',
        'failed' => 'warning',
    ];

    public function booted()
    {
        $this->authorize('admin');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedLogFile()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->perPage = 50;
        $this->setDisk();

        $this->logFiles = collect(Storage::disk('log')->files())
            ->filter(function ($filename) {
                return Str::endsWith($filename, '.log');
            })
            ->values()
            ->all();

        if(count($this->logFiles)) {
            $this->logFile = $this->logFiles[0];
        }

    }

    protected function setDisk()
    {
        $disks = config('filesystems.disks');
        $disks['log'] = [
            'driver' => 'local',
            'root' => storage_path('logs')
        ];
        Config::set('filesystems.disks', $disks);
    }

    protected function getLogArray()
    {
        $this->setDisk();

        $log = [];
        if($this->logFile) {
            $file = Storage::disk('log')->get($this->logFile);

            preg_match_all('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?\].*/', $file, $headings);

            if (!is_array($headings)) {
                return $log;
            }

            $log_data = preg_split('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?\].*/', $file);

            if ($log_data[0] < 1) {
                array_shift($log_data);
            }

            foreach ($headings as $h) {
                for ($i = 0, $j = count($h); $i < $j; $i++) {
                    foreach (array_keys($this->levels_imgs) as $level) {
                        if (strpos(strtolower($h[$i]), '.' . $level) || strpos(strtolower($h[$i]), $level . ':')) {

                            preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?)\](?:.*?(\w+)\.|.*?)' . $level . ': (.*?)( in .*?:[0-9]+)?$/i', $h[$i], $current);
                            if (!isset($current[4])) {
                                continue;
                            }

                            $log[] = array(
                                'context' => $current[3],
                                'level' => $level,
                                //'folder' => $this->folder,
                                'level_class' => $this->levels_classes[$level],
                                'level_img' => $this->levels_imgs[$level],
                                'date' => $current[1],
                                'text' => $current[4],
                                'in_file' => isset($current[5]) ? $current[5] : null,
                                //'stack' => preg_replace("/^\n*/", '', $log_data[$i])
                            );
                        }
                    }
                }
            }

            if (empty($log)) {

                $lines = explode(PHP_EOL, $file);
                $log = [];

                foreach ($lines as $key => $line) {
                    $log[] = [
                        'context' => '',
                        'level' => '',
                        'folder' => '',
                        'level_class' => '',
                        'level_img' => '',
                        'date' => $key + 1,
                        'text' => $line,
                        'in_file' => null,
                        'stack' => '',
                    ];
                }
            }
        }
        return array_reverse($log);
    }

    protected function getDataset()
    {
        $search = $this->search;

        $items = collect($this->getLogArray());
        if($search) {
            $items = $items->filter(function ($log) use ($search) {
                 return stristr($log['text'], $search);
             });
        }

        $items = $items->paginate($this->perPage);

        return $items;


    }

    public function render()
    {
        $items = $this->getDataSet();

        return view('log::views.log_app_table', compact('items'))
            ->layout('log::admin');
    }
}
