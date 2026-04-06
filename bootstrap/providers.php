<?php

use App\Infrastructure\Providers\RepositoryServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\ApplicationServiceProvider;
use App\Providers\FCMServiceProvider;

return [
    AppServiceProvider::class,
    ApplicationServiceProvider::class,
    AuthServiceProvider::class,
    RepositoryServiceProvider::class,
    FCMServiceProvider::class,
];
