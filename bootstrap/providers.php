<?php

use App\Infrastructure\Providers\RepositoryServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\ApplicationServiceProvider;

return [
    AppServiceProvider::class,
    ApplicationServiceProvider::class,
    AuthServiceProvider::class,
    RepositoryServiceProvider::class,
];
