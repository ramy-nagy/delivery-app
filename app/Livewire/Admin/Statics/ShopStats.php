<?php

namespace App\Livewire\Admin\Statics;

use App\Models\Shop;

class ShopStats extends BaseResourceStats
{
    protected function modelClass(): string
    {
        return Shop::class;
    }

    protected function isOpenColumn(): string
    {
        return 'is_open';
    }
}

