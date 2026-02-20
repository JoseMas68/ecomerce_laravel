<?php

return [
    App\Providers\AppServiceProvider::class,
    Laravel\Sanctum\SanctumServiceProvider::class,
    App\Infrastructure\Providers\RepositoryServiceProvider::class,
    App\Infrastructure\Providers\CatalogEventServiceProvider::class,
];
