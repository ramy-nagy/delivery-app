<?php

namespace App\Infrastructure\Providers;

use App\Contracts\Repositories\OrderRepositoryInterface as ContractsOrderRepository;
use App\Domain\Orders\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Repositories\OrderRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OrderRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(ContractsOrderRepository::class, OrderRepository::class);
    }
}
