<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register all repository bindings.
     */
    public function register(): void
    {
        // ──────────────────────────────────────────────
        // Catalog Domain
        // ──────────────────────────────────────────────
        $this->app->bind(
            \App\Domain\Catalog\Repositories\ProductRepositoryInterface::class,
            \App\Domain\Catalog\Repositories\EloquentProductRepository::class
        );

        $this->app->bind(
            \App\Domain\Catalog\Repositories\CategoryRepositoryInterface::class,
            \App\Domain\Catalog\Repositories\EloquentCategoryRepository::class
        );

        $this->app->bind(
            \App\Domain\Catalog\Repositories\BrandRepositoryInterface::class,
            \App\Domain\Catalog\Repositories\EloquentBrandRepository::class
        );

        // ──────────────────────────────────────────────
        // Cart Domain
        // ──────────────────────────────────────────────
        $this->app->bind(
            \App\Domain\Cart\Repositories\CartRepositoryInterface::class,
            \App\Domain\Cart\Repositories\EloquentCartRepository::class
        );

        // ──────────────────────────────────────────────
        // Orders Domain
        // ──────────────────────────────────────────────
        $this->app->bind(
            \App\Domain\Orders\Repositories\OrderRepositoryInterface::class,
            \App\Domain\Orders\Repositories\EloquentOrderRepository::class
        );

        // ──────────────────────────────────────────────
        // Users Domain
        // ──────────────────────────────────────────────
        $this->app->bind(
            \App\Domain\Users\Repositories\UserRepositoryInterface::class,
            \App\Domain\Users\Repositories\EloquentUserRepository::class
        );

        // ──────────────────────────────────────────────
        // Payments Domain
        // ──────────────────────────────────────────────
        $this->app->bind(
            \App\Domain\Payments\Repositories\PaymentRepositoryInterface::class,
            \App\Domain\Payments\Repositories\EloquentPaymentRepository::class
        );

        // Payment Gateways
        $this->app->bind(
            \App\Domain\Payments\Gateways\PaymentGatewayInterface::class,
            \App\Domain\Payments\Gateways\StripeGateway::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
