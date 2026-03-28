<?php

namespace Database\Factories;

use App\Models\RestaurantCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantCategory>
 */
class RestaurantCategoryFactory extends Factory
{
    protected $model = RestaurantCategory::class;

    public function definition(): array
    {
        $categories = [
            'مشويات',
            'كشري',
            'فول وفلافل',
            'أسماك',
            'بيتزا',
            'شاورما',
            'حلويات شرقية',
            'مأكولات بحرية',
            'وجبات سريعة',
            'عصائر طبيعية',
        ];
        $name = fake()->unique()->randomElement($categories);
        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'sort_order' => 0,
        ];
    }
}
