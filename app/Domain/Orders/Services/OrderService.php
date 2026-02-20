<?php

declare(strict_types=1);

namespace App\Domain\Orders\Services;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Services\CartService;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Domain\Orders\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        protected CartService $cartService
    ) {
    }

    /**
     * Create an order from the given cart
     *
     * @param Cart $cart
     * @param array $shippingData
     * @param string $paymentMethod
     * @return Order
     * @throws \Exception
     */
    public function createOrderFromCart(Cart $cart, array $shippingData, string $paymentMethod): Order
    {
        return DB::transaction(function () use ($cart, $shippingData, $paymentMethod) {
            $unavailableItems = $this->cartService->validateCartStock($cart);

            if (!empty($unavailableItems)) {
                throw new \Exception('Some items are out of stock');
            }

            $totals = $this->calculateOrderTotals($cart, $shippingData['shipping_method'] ?? 'standard');

            $orderData = [
                'user_id' => $cart->user_id,
                'order_number' => $this->generateOrderNumber(),
                'status' => OrderStatus::PENDING,
                'subtotal' => $totals['subtotal'],
                'shipping_cost' => $totals['shipping_cost'],
                'tax' => $totals['tax'],
                'discount' => $totals['discount'],
                'total' => $totals['total'],
                'currency' => 'EUR',
                'shipping_method' => $shippingData['shipping_method'] ?? 'standard',
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::PENDING,
                'shipping_address' => $shippingData['shipping_address'] ?? [],
                'billing_address' => $shippingData['billing_address'] ?? $shippingData['shipping_address'] ?? [],
                'notes' => $shippingData['notes'] ?? null,
            ];

            $order = Order::create($orderData);

            foreach ($cart->items as $cartItem) {
                $this->createOrderItem($order, $cartItem);

                $product = $cartItem->product;
                if ($product) {
                    $product->decrement('stock', $cartItem->quantity);
                }
            }

            $order->load('items');

            $this->cartService->clearCart($cart->id);

            event(new \App\Domain\Orders\Events\OrderCreated($order));

            \App\Jobs\ProcessOrderPaymentJob::dispatch($order);

            Log::info('Order created', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            return $order;
        });
    }

    /**
     * Get an order by ID with optional user verification
     *
     * @param int $orderId
     * @param int|null $userId
     * @return Order|null
     */
    public function getOrderById(int $orderId, ?int $userId = null): ?Order
    {
        $query = Order::with(['items', 'user', 'payments']);

        $order = $query->find($orderId);

        if (!$order) {
            return null;
        }

        if ($userId !== null && $order->user_id !== $userId) {
            return null;
        }

        return $order;
    }

    /**
     * Get orders for a user with optional status filter
     *
     * @param int $userId
     * @param string|null $status
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserOrders(int $userId, ?string $status = null, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Order::byUser($userId)
            ->with(['items', 'payments'])
            ->orderBy('created_at', 'desc');

        if ($status !== null) {
            try {
                $orderStatus = OrderStatus::from($status);
                $query->byStatus($orderStatus);
            } catch (\ValueError $e) {
                Log::warning('Invalid order status filter', ['status' => $status]);
            }
        }

        return $query->paginate($perPage);
    }

    /**
     * Update the status of an order
     *
     * @param int $orderId
     * @param string $status
     * @param string|null $notes
     * @param string|null $trackingNumber
     * @return bool
     * @throws \Exception
     */
    public function updateOrderStatus(int $orderId, string $status, ?string $notes = null, ?string $trackingNumber = null): bool
    {
        $order = Order::findOrFail($orderId);

        try {
            $newStatus = OrderStatus::from($status);
        } catch (\ValueError $e) {
            throw new InvalidArgumentException("Invalid order status: {$status}");
        }

        if (!$order->status->canTransitionTo($newStatus)) {
            throw new \Exception("Cannot transition from {$order->status->value} to {$status}");
        }

        $order->status = $newStatus;

        switch ($newStatus) {
            case OrderStatus::SHIPPED:
                $order->shipped_at = now();
                break;
            case OrderStatus::DELIVERED:
                $order->delivered_at = now();
                break;
            case OrderStatus::CANCELLED:
                $order->cancelled_at = now();
                break;
        }

        if ($notes !== null) {
            $currentNotes = $order->notes ?? '';
            $order->notes = $currentNotes . ($currentNotes ? "\n" : '') . $notes;
        }

        $order->save();

        $this->dispatchStatusEvent($order, $newStatus, $trackingNumber);

        Log::info('Order status updated', [
            'order_id' => $order->id,
            'old_status' => $order->getOriginal('status'),
            'new_status' => $status
        ]);

        return true;
    }

    /**
     * Cancel an order
     *
     * @param int $orderId
     * @param int $userId
     * @param string|null $reason
     * @return bool
     * @throws \Exception
     */
    public function cancelOrder(int $orderId, int $userId, ?string $reason = null): bool
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->firstOrFail();

        if (!$order->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled');
        }

        return DB::transaction(function () use ($order, $reason) {
            $order->status = OrderStatus::CANCELLED;
            $order->cancelled_at = now();

            $cancellationNote = 'Cancelled by user';
            if ($reason !== null) {
                $cancellationNote .= ": {$reason}";
            }

            $currentNotes = $order->notes ?? '';
            $order->notes = $currentNotes . ($currentNotes ? "\n" : '') . $cancellationNote;

            $order->save();

            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            $cancellationReason = $reason ?? 'User requested cancellation';
            event(new \App\Domain\Orders\Events\OrderCancelled($order, $cancellationReason));

            if ($order->payment_status === PaymentStatus::COMPLETED) {
                \App\Jobs\ProcessOrderRefundJob::dispatch($order);
            }

            Log::info('Order cancelled', ['order_id' => $order->id, 'reason' => $reason]);

            return true;
        });
    }

    /**
     * Calculate order totals based on cart and shipping method
     *
     * @param Cart $cart
     * @param string $shippingMethod
     * @return array
     */
    public function calculateOrderTotals(Cart $cart, string $shippingMethod = 'standard'): array
    {
        $subtotal = (float) $cart->items->sum('subtotal');

        $shippingCost = match ($shippingMethod) {
            'standard' => $subtotal >= 50 ? 0 : 4.99,
            'express' => $subtotal >= 50 ? 0 : 9.99,
            'free' => 0,
            default => 4.99,
        };

        $taxRate = 0.21;
        $tax = $subtotal * $taxRate;

        $discount = $cart->discount_amount ?? 0;

        $total = $subtotal + $shippingCost + $tax - $discount;

        return [
            'subtotal' => round($subtotal, 2),
            'shipping_cost' => round($shippingCost, 2),
            'tax' => round($tax, 2),
            'discount' => round($discount, 2),
            'total' => max(0, round($total, 2)),
        ];
    }

    /**
     * Generate a unique order number
     *
     * @return string
     */
    public function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');

        $lastOrder = Order::where('order_number', 'like', "{$prefix}{$date}%")
            ->lockForUpdate()
            ->orderBy('order_number', 'desc')
            ->first();

        $sequence = $lastOrder
            ? (int) substr($lastOrder->order_number, -4) + 1
            : 1;

        $orderNumber = sprintf('%s%s%04d', $prefix, $date, $sequence);

        if (Order::where('order_number', $orderNumber)->exists()) {
            return $this->generateOrderNumber();
        }

        return $orderNumber;
    }

    /**
     * Get order statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public function getOrderStats(int $userId): array
    {
        $orders = Order::byUser($userId)->get();

        $totalOrders = $orders->count();
        $pendingOrders = $orders->filter(fn ($order) => $order->status === OrderStatus::PENDING)->count();
        $completedOrders = $orders->filter(fn ($order) => $order->status === OrderStatus::DELIVERED)->count();
        $totalSpent = (float) $orders
            ->filter(fn ($order) => $order->payment_status === PaymentStatus::COMPLETED)
            ->sum('total');

        $averageOrderValue = $completedOrders > 0
            ? $totalSpent / $completedOrders
            : 0;

        return [
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'total_spent' => round($totalSpent, 2),
            'average_order_value' => round($averageOrderValue, 2),
        ];
    }

    /**
     * Create an order item from a cart item
     *
     * @param Order $order
     * @param \App\Domain\Cart\Models\CartItem $cartItem
     * @return OrderItem
     */
    protected function createOrderItem(Order $order, \App\Domain\Cart\Models\CartItem $cartItem): OrderItem
    {
        $product = $cartItem->product;

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $cartItem->product_id,
            'product_name' => $product ? $product->name : 'Unknown Product',
            'product_slug' => $product ? $product->slug : null,
            'quantity' => $cartItem->quantity,
            'unit_price' => $cartItem->unit_price,
            'subtotal' => $cartItem->subtotal,
            'tax' => $cartItem->subtotal * 0.21,
            'total' => $cartItem->subtotal + ($cartItem->subtotal * 0.21),
            'product_image' => $product ? $product->image : null,
        ]);

        return $orderItem;
    }

    /**
     * Dispatch the appropriate event based on order status
     *
     * @param Order $order
     * @param OrderStatus $status
     * @param string|null $trackingNumber
     * @return void
     */
    protected function dispatchStatusEvent(Order $order, OrderStatus $status, ?string $trackingNumber = null): void
    {
        switch ($status) {
            case OrderStatus::PROCESSING:
                event(new \App\Domain\Orders\Events\OrderProcessing($order));
                break;
            case OrderStatus::SHIPPED:
                $tracking = $trackingNumber ?? '';
                event(new \App\Domain\Orders\Events\OrderShipped($order, $tracking));
                break;
            case OrderStatus::DELIVERED:
                event(new \App\Domain\Orders\Events\OrderDelivered($order));
                break;
            case OrderStatus::CANCELLED:
                $reason = $order->notes ?? 'Order cancelled';
                event(new \App\Domain\Orders\Events\OrderCancelled($order, $reason));
                break;
            case OrderStatus::REFUNDED:
                $payment = $order->payments()->where('status', PaymentStatus::COMPLETED)->first();
                if ($payment) {
                    event(new \App\Domain\Orders\Events\OrderRefunded($order, $payment, $order->total));
                }
                break;
        }
    }
}
