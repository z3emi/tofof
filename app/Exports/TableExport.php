<?php

namespace App\Exports;

use DateTimeInterface;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class TableExport implements FromArray, WithHeadings, WithEvents
{
    protected array $headings;
    protected array $rows;

    public function __construct(array $headings, array $rows)
    {
        $this->headings = $headings;
        $this->rows = $rows;
    }

    public function array(): array
    {
        return array_map([$this, 'sanitizeRow'], $this->rows);
    }

    public function headings(): array
    {
        return $this->sanitizeRow($this->headings);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $dimension = $event->sheet->getDelegate()->calculateWorksheetDimension();

                if ($dimension) {
                    $event->sheet
                        ->getDelegate()
                        ->getStyle($dimension)
                        ->getNumberFormat()
                        ->setFormatCode('@');
                }
            },
        ];
    }

    protected function sanitizeRow(array $row): array
    {
        return array_map(function ($value) {
            if ($value instanceof DateTimeInterface) {
                return $value->format('Y-m-d H:i:s');
            }

            if (is_bool($value)) {
                return $value ? '1' : '0';
            }

            if (is_int($value)) {
                return (string) $value;
            }

            if (is_float($value)) {
                return rtrim(rtrim(sprintf('%.15F', $value), '0'), '.');
            }

            if (is_string($value) && is_numeric(str_replace(',', '', $value))) {
                return str_replace(',', '', trim($value));
            }

            if (is_array($value)) {
                return implode(', ', array_map('strval', $value));
            }

            $sanitized = preg_replace('/\s+/u', ' ', strip_tags((string) $value));

            return trim(is_string($sanitized) ? $sanitized : '');
        }, $row);
    }
}
