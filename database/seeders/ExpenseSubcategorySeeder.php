<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use Illuminate\Database\Seeder;

class ExpenseSubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $subcategories = [
            'Auto' => ['Tanken', 'Onderhoud', 'Verzekering', 'Parkeren', 'Wegenbelasting'],
            'Abbonnementen' => ['Streaming', 'Verzekeringen', 'Nutsvoorzieningen', 'Telecom', 'Giften & goede doelen', 'Overig'],
            'Overig' => ['Cadeaus', 'Belastingen & overheid', 'Diversen'],
        ];

        foreach ($subcategories as $categoryName => $names) {
            $category = ExpenseCategory::where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            foreach ($names as $name) {
                ExpenseSubcategory::firstOrCreate([
                    'expense_category_id' => $category->id,
                    'name' => $name,
                ]);
            }
        }
    }
}
