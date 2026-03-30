<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slider;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        $sliders = [
            [
                'title' => 'Promo 1',
                'image' => 'sliders/promo1.jpg',
                'link' => 'https://as2.ftcdn.net/jpg/04/17/20/77/1000_F_417207718_klR6e5n3f805BpalE91IeJaoNDyu3tNd.jpg',
                'type' => 'promotion',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Promo 2',
                'image' => 'sliders/promo2.jpg',
                'link' => 'https://as2.ftcdn.net/jpg/04/17/20/77/1000_F_417207718_klR6e5n3f805BpalE91IeJaoNDyu3tNd.jpg',
                'type' => 'promotion',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];
        foreach ($sliders as $slider) {
            Slider::create($slider);
        }
    }
}
