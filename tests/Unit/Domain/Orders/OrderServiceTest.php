<?php

namespace Tests\Unit\Domain\Orders;

use Tests\TestCase;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Cart\Services\CartService;
use App\Domain\Catalog\Models\Product;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentStatus;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Domain\Orders\Services\OrderService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
        $this->cartService = app(CartService::class);
        Queue::fake();
        Event::fake();
    }

    /**
     * Test creating an order from a cart successfully.
     */
    public function test_create_order_from_cart(): void
    {
        // Arrange
        Log::shouldReceive('info')->once();
        Event::fake([OrderCreated::class]);

        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();

        $product1 = Product::factory()->inStock()->create(['price' => 50.00, 'stock' => 10]);
        $product2 = Product::factory()->inStock()->create(['price' => 30.00, 'stock' => 5]);

        CartItem::factory()->forCart($cart)->forProduct($product1)->withQuantity(2)->create(); // 100.00
        CartItem::factory()->forCart($cart)->forProduct($product2)->withQuantity(1)->create(); // 30.00

        $shippingData = [
            'shipping_method' => 'standard',
            'shipping_address' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => '123 Main St',
                'city' => 'Madrid',
                'postal_code' => '28001',
                'province' => 'Madrid',
                'country' => 'España',
                'phone' => '+34600123456',
            ],
            'billing_address' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address_line_1' => '123 Main St',
                'city' => 'Madrid',
                'postal_code' => '28001',
                'province' => 'Madrid',
                'country' => 'España',
                'phone' => '+34600123456',
            ],
        ];

        // Act
        $order = $this->orderService->createOrderFromCart($cart, $shippingData, 'credit_card');

        // Assert
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertNotNull($order->order_number);
        $this->assertEquals(OrderStatus::PENDING, $order->status);
        $this->assertEquals(PaymentStatus::PENDING, $order->payment_status);
        $this->assertEquals('standard', $order->shipping_method);
        $this->assertEquals('credit_card', $order->payment_method);
        $this->assertEquals(130.00, $order->subtotal);
        $this->assertEquals(2, $order->items->count());
        $this->assertDatabaseHas('orders', ['order_number' => $order->order_number]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $product1->id]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $product2->id]);

        // Verify stock was decremented
        $this->assertEquals(8, $product1->fresh()->stock);
        $this->assertEquals(4, $product2->fresh()->stock);

        // Verify cart was cleared
        $this->assertEquals(0, $cart->items()->count());

        Event::assertDispatched(OrderCreated::class);
    }

    /**
     * Test creating order from cart validates stock availability.
     */
    public function test_create_order_from_cart_validates_stock(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();

        $product = Product::factory()->create(['stock' => 5]);
        CartItem::factory()->forCart($cart)->forProduct($product)->withQuantity(10)->create();

        $shippingData = [
            'shipping_method' => 'standard',
            'shipping_address' => ['address_line_1' => '123 Main St'],
        ];

        // Expect exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Some items are out of stock');

        // Act
        $this->orderService->createOrderFromCart($cart, $shippingData, 'credit_card');
    }

    /**
     * Test getting an order by ID successfully.
     */
    public function test_get_order_by_id(): void
    {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->create();

        // Act
        $foundOrder = $this->orderService->getOrderById($order->id);

        // Assert
        $this->assertInstanceOf(Order::class, $foundOrder);
        $this->assertEquals($order->id, $foundOrder->id);
        $this->assertEquals($order->order_number, $foundOrder->order_number);
    }

    /**
     * Test getting an order by ID with user verification.
     */
    public function test_get_order_by_id_with_user_verification(): void
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->forUser($user1)->create();

        // Act - Correct user
        $foundOrder = $this->orderService->getOrderById($order->id, $user1->id);
        $this->assertEquals($order->id, $foundOrder->id);

        // Act - Wrong user
        $notFoundOrder = $this->orderService->getOrderById($order->id, $user2->id);
        $this->assertNull($notFoundOrder);
    }

    /**
     * Test getting a non-existent order returns null.
     */
    public function test_get_non_existent_order_returns_null(): void
    {
        // Act
        $order = $this->orderService->getOrderById(999);

        // Assert
        $this->assertNull($order);
    }

    /**
     * Test getting orders for a user paginated.
     */
    public function test_get_user_orders(): void
    {
        // Arrange
        $user = User::factory()->create();
        Order::factory()->count(5)->forUser($user)->create();
        Order::factory()->count(3)->forUser(User::factory()->create())->create();

        // Act
        $orders = $this->orderService->getUserOrders($user->id);

        // Assert
        $this->assertEquals(5, $orders->total());
        $this->assertEquals(10, $orders->perPage()); // Default per page
    }

    /**
     * Test getting user orders filtered by status.
     */
    public function test_get_user_orders_filtered_by_status(): void
    {
        // Arrange
        $user = User::factory()->create();
        Order::factory()->count(3)->forUser($user)->pending()->create();
        Order::factory()->count(2)->forUser($user)->delivered()->create();

        // Act
        $pendingOrders = $this->orderService->getUserOrders($user->id, 'pending');

        // Assert
        $this->assertEquals(3, $pendingOrders->total());
        foreach ($pendingOrders->items() as $order) {
            $this->assertEquals(OrderStatus::PENDING, $order->status);
        }
    }

    /**
     * Test updating order status successfully.
     */
    public function test_update_order_status(): void
    {
        // Arrange
        Event::fake();
        Log::shouldReceive('info')->once();

        $order = Order::factory()->pending()->create();

        // Act
        $result = $this->orderService->updateOrderStatus($order->id, 'processing', 'Order is being processed');

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(OrderStatus::PROCESSING, $order->fresh()->status);
        $this->assertStringContainsString('Order is being processed', $order->fresh()->notes);
        Event::assertDispatched(\App\Domain\Orders\Events\OrderProcessing::class);
    }

    /**
     * Test updating order status with invalid status throws exception.
     */
    public function test_update_order_status_with_invalid_status_throws_exception(): void
    {
        // Arrange
        $order = Order::factory()->pending()->create();

        // Expect exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid order status');

        // Act
        $this->orderService->updateOrderStatus($order->id, 'invalid_status');
    }

    /**
     * Test updating order status with invalid transition throws exception.
     */
    public function test_update_order_status_with_invalid_transition_throws_exception(): void
    {
        // Arrange
        $order = Order::factory()->delivered()->create();

        // Expect exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot transition');

        // Act
        $this->orderService->updateOrderStatus($order->id, 'pending');
    }

    /**
     * Test updating order to shipped status sets shipped_at timestamp.
     */
    public function test_update_order_to_shipped_sets_timestamp(): void
    {
        // Arrange
        Event::fake();
        $order = Order::factory()->processing()->paid()->create();

        // Act
        $this->orderService->updateOrderStatus($order->id, 'shipped', null, 'TRACK123');

        // Assert
        $this->assertNotNull($order->fresh()->shipped_at);
        $this->assertEquals(OrderStatus::SHIPPED, $order->fresh()->status);
        Event::assertDispatched(\App\Domain\Orders\Events\OrderShipped::class);
    }

    /**
     * Test cancelling an order successfully.
     */
    public function test_cancel_order(): void
    {
        // Arrange
        Event::fake();
        Log::shouldReceive('info')->once();

        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->pending()->create();

        // Act
        $result = $this->orderService->cancelOrder($order->id, $user->id, 'Changed my mind');

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(OrderStatus::CANCELLED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->cancelled_at);
        $this->assertStringContainsString('Changed my mind', $order->fresh()->notes);
        Event::assertDispatched(\App\Domain\Orders\Events\OrderCancelled::class);
    }

    /**
     * Test cancelling order restores product stock.
     */
    public function test_cancel_order_restores_product_stock(): void
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 20]);
        $order = Order::factory()->forUser($user)->processing()->create();

        OrderItem::factory()->forOrder($order)->forProduct($product)->withQuantity(5)->create();

        $initialStock = $product->stock;

        // Act
        $this->orderService->cancelOrder($order->id, $user->id);

        // Assert
        $this->assertEquals($initialStock + 5, $product->fresh()->stock);
    }

    /**
     * Test cancelling order that cannot be cancelled throws exception.
     */
    public function test_cancel_order_that_cannot_be_cancelled_throws_exception(): void
    {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->delivered()->create();

        // Expect exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Order cannot be cancelled');

        // Act
        $this->orderService->cancelOrder($order->id, $user->id);
    }

    /**
     * Test cancelling order from different user throws exception.
     */
    public function test_cancel_order_from_different_user_throws_exception(): void
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->forUser($user1)->pending()->create();

        // Expect exception
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Act
        $this->orderService->cancelOrder($order->id, $user2->id);
    }

    /**
     * Test calculating order totals with standard shipping.
     */
    public function test_calculate_order_totals(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();

        CartItem::factory()->forCart($cart)->create([
            'quantity' => 2,
            'unit_price' => 50.00,
            'subtotal' => 100.00,
        ]);

        // Act
        $totals = $this->orderService->calculateOrderTotals($cart, 'standard');

        // Assert
        $this->assertEquals(100.00, $totals['subtotal']);
        $this->assertEquals(0.00, $totals['shipping_cost']); // Free shipping over 50
        $this->assertEquals(21.00, $totals['tax']); // 21%
        $this->assertEquals(0.00, $totals['discount']);
        $this->assertEquals(121.00, $totals['total']);
    }

    /**
     * Test calculating order totals with express shipping.
     */
    public function test_calculate_order_totals_with_express_shipping(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();

        CartItem::factory()->forCart($cart)->create([
            'quantity' => 1,
            'unit_price' => 30.00,
            'subtotal' => 30.00,
        ]);

        // Act
        $totals = $this->orderService->calculateOrderTotals($cart, 'express');

        // Assert
        $this->assertEquals(30.00, $totals['subtotal']);
        $this->assertEquals(9.99, $totals['shipping_cost']);
        $this->assertEquals(6.30, $totals['tax']);
        $this->assertEquals(46.29, $totals['total']);
    }

    /**
     * Test calculating order totals with discount.
     */
    public function test_calculate_order_totals_with_discount(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create(['discount_amount' => 10.00]);

        CartItem::factory()->forCart($cart)->create([
            'quantity' => 1,
            'unit_price' => 50.00,
            'subtotal' => 50.00,
        ]);

        // Act
        $totals = $this->orderService->calculateOrderTotals($cart, 'standard');

        // Assert
        $this->assertEquals(50.00, $totals['subtotal']);
        $this->assertEquals(10.00, $totals['discount']);
        $this->assertEquals(60.50, $totals['total']); // 50 + 0 + 10.5 - 10
    }

    /**
     * Test generating order number is unique.
     */
    public function test_generate_order_number_is_unique(): void
    {
        // Act - Generate multiple order numbers
        $orderNumber1 = $this->orderService->generateOrderNumber();
        $orderNumber2 = $this->orderService->generateOrderNumber();
        $orderNumber3 = $this->orderService->generateOrderNumber();

        // Assert
        $this->assertNotEmpty($orderNumber1);
        $this->assertNotEmpty($orderNumber2);
        $this->assertNotEmpty($orderNumber3);
        $this->assertNotEquals($orderNumber1, $orderNumber2);
        $this->assertNotEquals($orderNumber2, $orderNumber3);

        // Verify format: ORD + date (8 digits) + sequence (4 digits)
        $this->assertMatchesRegularExpression('/^ORD\d{12}$/', $orderNumber1);
    }

    /**
     * Test getting order statistics for a user.
     */
    public function test_get_order_stats(): void
    {
        // Arrange
        $user = User::factory()->create();

        Order::factory()->count(3)->forUser($user)->pending()->create();
        Order::factory()->count(2)->forUser($user)->delivered()->paid()->create([
            'total' => 100.00,
        ]);
        Order::factory()->count(1)->forUser($user)->delivered()->paid()->create([
            'total' => 50.00,
        ]);

        // Act
        $stats = $this->orderService->getOrderStats($user->id);

        // Assert
        $this->assertEquals(6, $stats['total_orders']);
        $this->assertEquals(3, $stats['pending_orders']);
        $this->assertEquals(3, $stats['completed_orders']);
        $this->assertEquals(150.00, $stats['total_spent']);
        $this->assertEquals(50.00, $stats['average_order_value']);
    }

    /**
     * Test getting order stats for user with no orders.
     */
    public function test_get_order_stats_for_user_with_no_orders(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $stats = $this->orderService->getOrderStats($user->id);

        // Assert
        $this->assertEquals(0, $stats['total_orders']);
        $this->assertEquals(0, $stats['pending_orders']);
        $this->assertEquals(0, $stats['completed_orders']);
        $this->assertEquals(0.00, $stats['total_spent']);
        $this->assertEquals(0.00, $stats['average_order_value']);
    }

    /**
     * Test cancelling paid order dispatches refund job.
     */
    public function test_cancelling_paid_order_dispatches_refund_job(): void
    {
        // Arrange
        Queue::fake();
        Event::fake();
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->processing()->create([
            'payment_status' => PaymentStatus::COMPLETED,
        ]);

        // Act
        $this->orderService->cancelOrder($order->id, $user->id);

        // Assert
        Queue::assertPushed(\App\Jobs\ProcessOrderRefundJob::class, function ($job) use ($order) {
            return $job->order->id === $order->id;
        });
    }

    /**
     * Test order created dispatches payment job.
     */
    public function test_order_created_dispatches_payment_job(): void
    {
        // Arrange
        Queue::fake();
        Log::shouldReceive('info')->once();

        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->inStock()->create();
        CartItem::factory()->forCart($cart)->forProduct($product)->create();

        $shippingData = [
            'shipping_method' => 'standard',
            'shipping_address' => ['address_line_1' => '123 Main St'],
        ];

        // Act
        $this->orderService->createOrderFromCart($cart, $shippingData, 'credit_card');

        // Assert
        Queue::assertPushed(\App\Jobs\ProcessOrderPaymentJob::class);
    }

    /**
     * Test updating order status to delivered sets delivered_at timestamp.
     */
    public function test_update_order_to_delivered_sets_timestamp(): void
    {
        // Arrange
        Event::fake();
        $order = Order::factory()->shipped()->create();

        // Act
        $this->orderService->updateOrderStatus($order->id, 'delivered');

        // Assert
        $this->assertNotNull($order->fresh()->delivered_at);
        $this->assertEquals(OrderStatus::DELIVERED, $order->fresh()->status);
        Event::assertDispatched(\App\Domain\Orders\Events\OrderDelivered::class);
    }

    /**
     * Test updating order status appends notes to existing notes.
     */
    public function test_update_order_status_appends_notes(): void
    {
        // Arrange
        $order = Order::factory()->pending()->create(['notes' => 'Original note']);

        // Act
        $this->orderService->updateOrderStatus($order->id, 'processing', 'Additional note');

        // Assert
        $this->assertStringContainsString('Original note', $order->fresh()->notes);
        $this->assertStringContainsString('Additional note', $order->fresh()->notes);
    }
}
