<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuCategory;

class MainCategorySeeder extends Seeder
{
    public function run(): void
    {
        $mainCategories = [
            ['name' => 'مطاعم', 'slug' => 'restaurants', 'sort_order' => 1],
            ['name' => 'محلات', 'slug' => 'shops', 'sort_order' => 2],
            ['name' => 'صيدليات', 'slug' => 'pharmacies', 'sort_order' => 3],
        ];
        foreach ($mainCategories as $cat) {
            MenuCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
