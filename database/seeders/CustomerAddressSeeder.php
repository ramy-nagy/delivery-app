<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CustomerAddress;
use Illuminate\Database\Seeder;

class CustomerAddressSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'john@example.com')->first();

        if (!$user) {
            return;
        }

        // Create addresses in Arabic
        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id, 'label' => 'البيت'],
            [
                'label' => 'البيت',
                'line1' => '123 شارع النيل، فلة 4B',
                'city' => 'القاهرة',
                'governorate' => 'القاهرة',
                'is_default' => true,
            ]
        );

        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id, 'label' => 'العمل'],
            [
                'label' => 'العمل',
                'line1' => '456 شارع المعادي، المكتب 202',
                'city' => 'الجيزة',
                'governorate' => 'الجيزة',
                'is_default' => false,
            ]
        );

        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id, 'label' => 'منزل الأسرة'],
            [
                'label' => 'منزل الأسرة',
                'line1' => '789 شارع الهرم، شقة 101',
                'city' => 'السادس من أكتوبر',
                'governorate' => 'الجيزة',
                'is_default' => false,
            ]
        );
    }
}
