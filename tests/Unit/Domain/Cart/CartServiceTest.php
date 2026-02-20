<?php

namespace Tests\Unit\Domain\Cart;

use Tests\TestCase;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Cart\Services\CartService;
use App\Domain\Catalog\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
    }

    /**
     * Test creating a cart for an authenticated user.
     */
    public function test_create_cart_for_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $cart = $this->cartService->getOrCreateCart($user->id);

        // Assert
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals($user->id, $cart->user_id);
        $this->assertNull($cart->session_id);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test creating a cart for a guest user with session ID.
     */
    public function test_create_cart_for_guest(): void
    {
        // Arrange
        $sessionId = 'guest_session_12345';

        // Act
        $cart = $this->cartService->getOrCreateCart(null, $sessionId);

        // Assert
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertNull($cart->user_id);
        $this->assertNotNull($cart->session_id);
        $this->assertDatabaseHas('carts', [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Test adding an item to the cart successfully.
     */
    public function test_add_item_to_cart(): void
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->inStock()->create(['stock' => 10]);

        // Act
        $cartItem = $this->cartService->addItem($cart, $product->id, 2);

        // Assert
        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals($cart->id, $cartItem->cart_id);
        $this->assertEquals($product->id, $cartItem->product_id);
        $this->assertEquals(2, $cartItem->quantity);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        Event::assertDispatched(\App\Domain\Cart\Events\ItemAdded::class);
    }

    /**
     * Test adding an item updates quantity if the product already exists in cart.
     */
    public function test_add_item_updates_quantity_if_exists(): void
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->inStock()->create(['stock' => 20]);

        // Act - Add item first time
        $this->cartService->addItem($cart, $product->id, 2);
        $cart->load('items.product');

        // Act - Add same item again
        $cartItem = $this->cartService->addItem($cart, $product->id, 3);

        // Assert
        $this->assertEquals(5, $cartItem->quantity);
        $this->assertEquals(1, CartItem::where('cart_id', $cart->id)->where('product_id', $product->id)->count());
    }

    /**
     * Test adding item with insufficient stock throws exception.
     */
    public function test_add_item_with_insufficient_stock_throws_exception(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->create(['stock' => 5]);

        // Expect exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock for product');

        // Act
        $this->cartService->addItem($cart, $product->id, 10);
    }

    /**
     * Test updating item quantity successfully.
     */
    public function test_update_item_quantity(): void
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->inStock()->create(['stock' => 15]);
        $cartItem = CartItem::factory()->forCart($cart)->forProduct($product)->withQuantity(3)->create();

        // Act
        $result = $this->cartService->updateItemQuantity($cart->id, $cartItem->id, 7);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 7,
        ]);
        Event::assertDispatched(\App\Domain\Cart\Events\CartUpdated::class);
    }

    /**
     * Test updating quantity to zero removes the item.
     */
    public function test_update_item_quantity_to_zero_removes_item(): void
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->inStock()->create();
        $cartItem = CartItem::factory()->forCart($cart)->forProduct($product)->withQuantity(3)->create();

        // Act
        $result = $this->cartService->updateItemQuantity($cart->id, $cartItem->id, 0);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    /**
     * Test updating quantity beyond available stock throws exception.
     */
    public function test_update_item_quantity_beyond_stock_throws_exception(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->create(['stock' => 5]);
        $cartItem = CartItem::factory()->forCart($cart)->forProduct($product)->withQuantity(2)->create();

        // Expect exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock for product');

        // Act
        $this->cartService->updateItemQuantity($cart->id, $cartItem->id, 10);
    }

    /**
     * Test removing an item from the cart.
     */
    public function test_remove_item_from_cart(): void
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->inStock()->create();
        $cartItem = CartItem::factory()->forCart($cart)->forProduct($product)->create();

        // Act
        $result = $this->cartService->removeItem($cart->id, $cartItem->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
        Event::assertDispatched(\App\Domain\Cart\Events\ItemRemoved::class);
    }

    /**
     * Test clearing all items from the cart.
     */
    public function test_clear_cart(): void
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        CartItem::factory()->count(3)->forCart($cart)->create();

        // Act
        $result = $this->cartService->clearCart($cart->id);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals(0, $cart->items()->count());
        Event::assertDispatched(\App\Domain\Cart\Events\CartUpdated::class);
    }

    /**
     * Test getting cart total amount.
     */
    public function test_get_cart_total(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();

        $item1 = CartItem::factory()->forCart($cart)->create([
            'quantity' => 2,
            'unit_price' => 10.00,
            'subtotal' => 20.00,
        ]);

        $item2 = CartItem::factory()->forCart($cart)->create([
            'quantity' => 3,
            'unit_price' => 15.00,
            'subtotal' => 45.00,
        ]);

        // Act
        $total = $this->cartService->getCartTotal($cart);

        // Assert
        $this->assertEquals(65.00, $total);
    }

    /**
     * Test getting cart items count (total quantity).
     */
    public function test_get_cart_items_count(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();

        CartItem::factory()->forCart($cart)->create(['quantity' => 2]);
        CartItem::factory()->forCart($cart)->create(['quantity' => 3]);
        CartItem::factory()->forCart($cart)->create(['quantity' => 1]);

        // Act
        $count = $this->cartService->getCartItemsCount($cart);

        // Assert
        $this->assertEquals(6, $count);
    }

    /**
     * Test merging session cart to user cart successfully.
     */
    public function test_merge_session_cart_to_user(): void
    {
        // Arrange
        Event::fake();
        $sessionId = 'guest_session_' . uniqid();
        $user = User::factory()->create();

        // Create session cart with items
        $sessionCart = Cart::factory()->forGuest()->create(['session_id' => $sessionId]);
        $product1 = Product::factory()->inStock()->create();
        $product2 = Product::factory()->inStock()->create();

        CartItem::factory()->forCart($sessionCart)->forProduct($product1)->withQuantity(2)->create();
        CartItem::factory()->forCart($sessionCart)->forProduct($product2)->withQuantity(1)->create();

        // Create user cart
        $userCart = Cart::factory()->forUser($user)->create();
        $product3 = Product::factory()->inStock()->create();
        CartItem::factory()->forCart($userCart)->forProduct($product3)->withQuantity(1)->create();

        // Act
        $mergedCart = $this->cartService->mergeSessionCartToUser($sessionId, $user->id);

        // Assert
        $this->assertEquals($user->id, $mergedCart->user_id);
        $this->assertEquals(3, $mergedCart->items->count()); // 2 from session + 1 from user
        $this->assertDatabaseMissing('carts', ['id' => $sessionCart->id]);
        Event::assertDispatched(\App\Domain\Cart\Events\CartUpdated::class);
    }

    /**
     * Test merging session cart when products overlap, quantities are summed.
     */
    public function test_merge_session_cart_to_user_sums_quantities_for_same_products(): void
    {
        // Arrange
        Event::fake();
        $sessionId = 'guest_session_' . uniqid();
        $user = User::factory()->create();
        $product = Product::factory()->inStock()->create(['stock' => 20]);

        // Create session cart with product
        $sessionCart = Cart::factory()->forGuest()->create(['session_id' => $sessionId]);
        CartItem::factory()->forCart($sessionCart)->forProduct($product)->withQuantity(2)->create();

        // Create user cart with same product
        $userCart = Cart::factory()->forUser($user)->create();
        CartItem::factory()->forCart($userCart)->forProduct($product)->withQuantity(3)->create();

        // Act
        $mergedCart = $this->cartService->mergeSessionCartToUser($sessionId, $user->id);

        // Assert
        $this->assertEquals(1, $mergedCart->items->count());
        $this->assertEquals(5, $mergedCart->items->first()->quantity);
    }

    /**
     * Test merging when session cart doesn't exist returns user cart.
     */
    public function test_merge_session_cart_when_session_cart_doesnt_exist(): void
    {
        // Arrange
        $user = User::factory()->create();
        $sessionId = 'non_existent_session';
        Cart::factory()->forUser($user)->create();

        // Act
        $cart = $this->cartService->mergeSessionCartToUser($sessionId, $user->id);

        // Assert
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals($user->id, $cart->user_id);
    }

    /**
     * Test validating cart stock when all items have sufficient stock.
     */
    public function test_validate_cart_stock_with_sufficient_stock(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->create(['stock' => 10]);
        CartItem::factory()->forCart($cart)->forProduct($product)->withQuantity(5)->create();

        // Act
        $unavailableItems = $this->cartService->validateCartStock($cart);

        // Assert
        $this->assertIsArray($unavailableItems);
        $this->assertEmpty($unavailableItems);
    }

    /**
     * Test validating cart stock when items have insufficient stock.
     */
    public function test_validate_cart_stock_with_insufficient_stock(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();

        $product1 = Product::factory()->create(['stock' => 5]);
        $product2 = Product::factory()->create(['stock' => 3]);

        CartItem::factory()->forCart($cart)->forProduct($product1)->withQuantity(10)->create();
        CartItem::factory()->forCart($cart)->forProduct($product2)->withQuantity(5)->create();

        // Act
        $unavailableItems = $this->cartService->validateCartStock($cart);

        // Assert
        $this->assertIsArray($unavailableItems);
        $this->assertCount(2, $unavailableItems);
        $this->assertEquals(10, $unavailableItems[0]['requested']);
        $this->assertEquals(5, $unavailableItems[0]['available']);
        $this->assertEquals(5, $unavailableItems[1]['requested']);
        $this->assertEquals(3, $unavailableItems[1]['available']);
    }

    /**
     * Test applying a coupon code to the cart.
     */
    public function test_apply_coupon_to_cart(): void
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $couponCode = 'SAVE10';

        // Act
        $result = $this->cartService->applyCoupon($cart, $couponCode);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'coupon_code' => $couponCode,
        ]);
        Event::assertDispatched(\App\Domain\Cart\Events\CartUpdated::class);
    }

    /**
     * Test removing a coupon from the cart.
     */
    public function test_remove_coupon_from_cart(): void
    {
        // Arrange
        Event::fake();
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create([
            'coupon_code' => 'SAVE10',
            'discount_amount' => 10.00,
        ]);

        // Act
        $result = $this->cartService->removeCoupon($cart);

        // Assert
        $this->assertTrue($result);
        $this->assertNull($cart->fresh()->coupon_code);
        $this->assertEquals(0, $cart->fresh()->discount_amount);
        Event::assertDispatched(\App\Domain\Cart\Events\CartUpdated::class);
    }

    /**
     * Test getting cart summary with all details.
     */
    public function test_get_cart_summary(): void
    {
        // Arrange
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->inStock()->create(['name' => 'Test Product']);
        CartItem::factory()->forCart($cart)->forProduct($product)->withQuantity(2)->create();

        // Act
        $summary = $this->cartService->getCartSummary($cart);

        // Assert
        $this->assertIsArray($summary);
        $this->assertEquals($cart->id, $summary['cart_id']);
        $this->assertEquals(2, $summary['items_count']);
        $this->assertArrayHasKey('subtotal', $summary);
        $this->assertArrayHasKey('discount', $summary);
        $this->assertArrayHasKey('total', $summary);
        $this->assertArrayHasKey('items', $summary);
        $this->assertCount(1, $summary['items']);
    }

    /**
     * Test getting or creating cart returns existing cart for user.
     */
    public function test_get_or_create_cart_returns_existing_cart_for_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $existingCart = Cart::factory()->forUser($user)->create();

        // Act
        $cart = $this->cartService->getOrCreateCart($user->id);

        // Assert
        $this->assertEquals($existingCart->id, $cart->id);
        $this->assertEquals(1, Cart::where('user_id', $user->id)->count());
    }

    /**
     * Test getting or creating cart returns existing cart for session.
     */
    public function test_get_or_create_cart_returns_existing_cart_for_session(): void
    {
        // Arrange
        $sessionId = 'session_' . uniqid();
        $existingCart = Cart::factory()->forGuest()->create(['session_id' => $sessionId]);

        // Act
        $cart = $this->cartService->getOrCreateCart(null, $sessionId);

        // Assert
        $this->assertEquals($existingCart->id, $cart->id);
    }

    /**
     * Test adding item to non-existent cart fails.
     */
    public function test_add_item_to_non_existent_cart(): void
    {
        // Arrange
        $product = Product::factory()->inStock()->create();

        // Expect exception
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Act
        $cart = new Cart(['id' => 999]);
        $this->cartService->addItem($cart, $product->id, 1);
    }
}
