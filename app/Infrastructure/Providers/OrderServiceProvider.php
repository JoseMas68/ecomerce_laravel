<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Orders\Repositories\EloquentOrderRepository;
use App\Domain\Orders\Repositories\OrderRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            OrderRepositoryInterface::class,
            EloquentOrderRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations for this domain
        $this->loadMigrationsFrom(database_path('migrations/domain/orders'));
    }
}
