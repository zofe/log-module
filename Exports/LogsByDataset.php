<?php

namespace App\Modules\Log\Exports;

use Illuminate\Support\Facades\Blade;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LogsByDataset implements FromCollection, WithHeadings, WithColumnFormatting, WithMapping, ShouldAutoSize
{
    public $dataset = [];

    public function __construct($dataset)
    {
        $this->dataset = $dataset;
    }

    public function collection()
    {
        return $this->dataset;
    }

    public function headings(): array
    {
        $headings = [
            'author',
            'subject_type',
            'subject_name',
            'log_name',
            'action_message',
            'action_object',
            'created_at'
        ];

        return $headings;
    }

    public function map($item): array
    {
        $author = ltrim(str_replace("\n", '', strip_tags(Blade::render('<x-log::causer-user :log="$log"/>', ['log' => $item]))));
        $loggedObject = explode(':', str_replace(' ', '', str_replace("\n", '', strip_tags(Blade::render('<x-log::logged-object :log="$log"/>', ['log' => $item])))));
        $subject_type = $loggedObject[0];
        $subject_name = $loggedObject[1];
        $property = collect($item->properties->toArray())->mapWithKeys(function($item, $key){
            if(in_array($key, ['from', 'to', 'added', 'settings'])){
                $item = collect($item)->mapWithKeys(function($item2, $key2){
                    return [str_replace('key_', '', str_replace(":", "", $key2)) => $item2];
                })->toArray();
            }
            return [str_replace(":", "", $key) => $item];
        })->toArray();
        $columns = [
            $author,
            $subject_type,
            $subject_name,
            __("log::title.{$item->log_name}"),
            \App\Modules\Log\Services\ActivityLogService::makeActivityLogReadable($item),
            $property,
            $item->created_at->format('d/m/Y H:i:s')
        ];

        return $columns;
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY
        ];
    }
}
