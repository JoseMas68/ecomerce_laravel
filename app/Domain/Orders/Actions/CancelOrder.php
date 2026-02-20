<?php

declare(strict_types=1);

namespace App\Domain\Orders\Actions;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Events\OrderCancelled;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelOrder
{
    public function execute(Order $order, string $reason = 'Order cancelled by customer'): Order
    {
        try {
            DB::beginTransaction();

            if (!$order->canBeCancelled()) {
                throw new \Exception('Order cannot be cancelled. Current status: ' . $order->status->value);
            }

            $previousStatus = $order->status;
            $order->status = OrderStatus::CANCELLED;
            $order->cancelled_at = now();
            $order->save();

            $this->restoreProductStock($order);

            DB::commit();

            event(new OrderCancelled($order, $reason));

            Log::info('Order cancelled', [
                'order_id' => $order->id,
                'previous_status' => $previousStatus->value,
                'reason' => $reason,
            ]);

            return $order->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to cancel order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function restoreProductStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);

                Log::info('Product stock restored', [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'order_id' => $order->id,
                ]);
            }
        }
    }
}
