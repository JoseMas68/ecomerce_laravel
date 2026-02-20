<?php

declare(strict_types=1);

namespace App\Domain\Orders\Jobs;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Order $order,
        public OrderStatus $newStatus,
        public ?string $reason = null
    ) {
    }

    public function handle(): void
    {
        try {
            DB::beginTransaction();

            if (!$this->order->updateStatus($this->newStatus)) {
                throw new \Exception('Cannot transition order from ' . $this->order->status->value . ' to ' . $this->newStatus->value);
            }

            match ($this->newStatus) {
                OrderStatus::CANCELLED => $this->handleCancellation(),
                OrderStatus::SHIPPED => $this->handleShipment(),
                OrderStatus::DELIVERED => $this->handleDelivery(),
                default => null,
            };

            DB::commit();

            Log::info('Order status updated', [
                'order_id' => $this->order->id,
                'old_status' => $this->order->getOriginal('status'),
                'new_status' => $this->newStatus->value,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update order status', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function handleCancellation(): void
    {
        $this->order->cancelled_at = now();
        $this->order->save();

        foreach ($this->order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        event(new \App\Domain\Orders\Events\OrderCancelled($this->order, $this->reason ?? 'Order cancelled'));
    }

    private function handleShipment(): void
    {
        $this->order->shipped_at = now();
        $this->order->save();

        event(new \App\Domain\Orders\Events\OrderShipped($this->order, $this->reason ?? ''));
    }

    private function handleDelivery(): void
    {
        $this->order->delivered_at = now();
        $this->order->save();

        event(new \App\Domain\Orders\Events\OrderDelivered($this->order));
    }
}
