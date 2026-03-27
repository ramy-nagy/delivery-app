<?php

namespace App\Providers;

use App\Models\Restaurant;
use App\Models\Shop;
use App\Policies\RestaurantPolicy;
use App\Policies\ShopPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Restaurant::class => RestaurantPolicy::class,
        Shop::class => ShopPolicy::class,
    ];
}

