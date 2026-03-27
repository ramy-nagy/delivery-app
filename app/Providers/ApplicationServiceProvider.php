<?php

namespace App\Providers;

use App\Application\Orders\Actions\CreateOrderAction;
use App\Application\Orders\Contracts\CreateOrderActionInterface;
use Illuminate\Support\ServiceProvider;

class ApplicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CreateOrderActionInterface::class, CreateOrderAction::class);
        $this->app->singleton(CreateOrderAction::class);
    }
}
