<?php

declare(strict_types=1);

namespace App\Domain\Orders\Actions;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Catalog\Models\Product;
use App\Domain\Orders\DTOs\OrderData;
use App\Domain\Orders\DTOs\OrderItemData;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Events\OrderCreated;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateOrderFromCart
{
    public function execute(Cart $cart, OrderData $orderData): Order
    {
        try {
            DB::beginTransaction();

            $this->validateCart($cart);

            $order = $this->createOrder($cart, $orderData);
            $this->createOrderItems($order, $cart->items);
            $order->calculateTotals();
            $order->save();

            $this->updateProductStock($cart->items);
            $cart->clear();

            DB::commit();

            event(new OrderCreated($order));

            Log::info('Order created from cart', [
                'order_id' => $order->id,
                'cart_id' => $cart->id,
                'user_id' => $order->user_id,
            ]);

            return $order;
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to create order from cart', [
                'cart_id' => $cart->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function validateCart(Cart $cart): void
    {
        if ($cart->items->isEmpty()) {
            throw new \Exception('Cannot create order from empty cart');
        }

        foreach ($cart->items as $item) {
            if (!$item->product) {
                throw new \Exception("Product not found for cart item {$item->id}");
            }

            if (!$item->isInStock()) {
                throw new \Exception("Insufficient stock for product: {$item->product->name}");
            }
        }
    }

    private function createOrder(Cart $cart, OrderData $orderData): Order
    {
        return Order::create([
            'user_id' => $orderData->userId,
            'status' => OrderStatus::PENDING,
            'shipping_method' => $orderData->shippingMethod,
            'payment_method' => $orderData->paymentMethod,
            'shipping_cost' => $orderData->shippingCost,
            'discount' => $orderData->discount ?? 0,
            'currency' => $orderData->currency,
            'shipping_address' => $orderData->shippingAddress->toArray(),
            'billing_address' => $orderData->billingAddress->toArray(),
            'notes' => $orderData->notes,
        ]);
    }

    private function createOrderItems(Order $order, Collection $cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            $itemData = OrderItemData::fromCartItem($cartItem);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $itemData->productId,
                'product_name' => $itemData->productName,
                'product_slug' => $itemData->productSlug,
                'quantity' => $itemData->quantity,
                'unit_price' => $itemData->unitPrice,
                'product_image' => $itemData->productImage,
            ]);
        }
    }

    private function updateProductStock(Collection $cartItems): void
    {
        foreach ($cartItems as $item) {
            if ($item->product) {
                $item->product->decrement('stock', $item->quantity);
            }
        }
    }
}
