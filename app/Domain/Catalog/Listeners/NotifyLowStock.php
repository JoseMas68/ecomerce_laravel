<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Listeners;

use App\Domain\Catalog\Events\StockLowUpdated;
use Illuminate\Support\Facades\Log;

/**
 * Listener to notify when product stock is low
 */
class NotifyLowStock
{
    /**
     * Handle the event.
     *
     * @param StockLowUpdated $event
     * @return void
     */
    public function handle(StockLowUpdated $event): void
    {
        $product = $event->product;

        // Log the low stock notification
        Log::warning('Low stock alert', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'current_stock' => $product->stock,
            'threshold' => 10,
        ]);

        // TODO: Implement additional notification methods:
        // - Send email to admin
        // - Send Slack/Teams notification
        // - Create notification in database
        // - Trigger webhook to external service

        // Example: Send email (would need to implement a Notification class)
        // \Notification::route('mail', 'admin@example.com')
        //     ->notify(new LowStockNotification($product));

        // Example: Send Slack notification
        // \Slack::send("Low stock alert: Product {$product->name} has only {$product->stock} units left.");
    }
}
