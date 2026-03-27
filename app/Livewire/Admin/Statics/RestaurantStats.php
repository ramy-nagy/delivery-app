<?php

namespace App\Livewire\Admin\Statics;

use App\Models\Restaurant;

class RestaurantStats extends BaseResourceStats
{
    protected function modelClass(): string
    {
        return Restaurant::class;
    }

    protected function isOpenColumn(): string
    {
        return 'is_open';
    }
}

