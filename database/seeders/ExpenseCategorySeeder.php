<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    $categories = [
        ['code' => '5001', 'name' => ['en' => 'Office Rent', 'ku' => 'کرێی ئۆفیس']],
        ['code' => '5002', 'name' => ['en' => 'Electricity', 'ku' => 'کارەبا']],
        ['code' => '5003', 'name' => ['en' => 'Water & Services', 'ku' => 'ئاو و خزمەتگوزاری']],
        ['code' => '6001', 'name' => ['en' => 'Staff Salaries', 'ku' => 'مووچەی فەرمانبەران']],
        ['code' => '7001', 'name' => ['en' => 'Marketing & Ads', 'ku' => 'مارکێتینگ و ڕیکلام']],
        ['code' => '8001', 'name' => ['en' => 'Office Supplies', 'ku' => 'پێداویستی ئۆفیس']],
    ];

    foreach ($categories as $cat) {
        \App\Models\ExpenseCategory::create($cat);
    }
}
}
