<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'OPS', 'name' => 'مصاريف تشغيلية', 'description' => 'الإيجارات والخدمات والصيانة الدورية.'],
            ['code' => 'PAY', 'name' => 'رواتب ومستحقات', 'description' => 'رواتب الموظفين والحوافز والمكافآت.'],
            ['code' => 'LOG', 'name' => 'نقل وتوريد', 'description' => 'الشحن، الوقود، أجور النقل والتوريد.'],
            ['code' => 'MKT', 'name' => 'تسويق ومبيعات', 'description' => 'الإعلانات، الحملات التسويقية، المواد الدعائية.'],
        ];

        foreach ($categories as $attributes) {
            ExpenseCategory::updateOrCreate(['code' => $attributes['code']], [
                'name' => $attributes['name'],
                'description' => $attributes['description'],
                'is_active' => true,
            ]);
        }
    }
}
