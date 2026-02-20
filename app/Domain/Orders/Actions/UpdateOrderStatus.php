<?php

declare(strict_types=1);

namespace App\Domain\Orders\Actions;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Events\OrderDelivered;
use App\Domain\Orders\Events\OrderShipped;
use App\Domain\Orders\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatus
{
    public function execute(Order $order, OrderStatus $newStatus, ?string $reason = null): Order
    {
        try {
            DB::beginTransaction();

            if (!$order->status->canTransitionTo($newStatus)) {
                throw new \Exception(
                    "Cannot transition order from {$order->status->value} to {$newStatus->value}"
                );
            }

            $previousStatus = $order->status;
            $order->status = $newStatus;

            match ($newStatus) {
                OrderStatus::SHIPPED => $this->handleShipment($order),
                OrderStatus::DELIVERED => $this->handleDelivery($order),
                OrderStatus::PROCESSING => null,
                default => null,
            };

            $order->save();

            DB::commit();

            Log::info('Order status updated', [
                'order_id' => $order->id,
                'previous_status' => $previousStatus->value,
                'new_status' => $newStatus->value,
                'reason' => $reason,
            ]);

            return $order->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update order status', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function handleShipment(Order $order): void
    {
        $order->shipped_at = now();

        event(new OrderShipped($order, $order->shipping_address['tracking_number'] ?? ''));
    }

    private function handleDelivery(Order $order): void
    {
        $order->delivered_at = now();

        event(new OrderDelivered($order));
    }
}
