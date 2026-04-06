<?php

namespace App\Providers;

use App\Services\FCMService;
use App\Infrastructure\Repositories\FCMRepository;
use Illuminate\Support\ServiceProvider;

class FCMServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FCMService::class, function ($app) {
            return new FCMService();
        });

        $this->app->singleton('fcm', function ($app) {
            return $app->make(FCMService::class);
        });

        $this->app->singleton(FCMRepository::class, function ($app) {
            return new FCMRepository();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
