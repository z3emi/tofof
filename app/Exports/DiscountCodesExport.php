<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DiscountCodesExport implements FromArray, WithHeadings
{
    protected array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'الكود',
            'النوع',
            'القيمة',
            'مرات الاستخدام',
            'الحد الأقصى للاستخدام',
            'الحالة',
            'تاريخ الانتهاء',
        ];
    }
}
