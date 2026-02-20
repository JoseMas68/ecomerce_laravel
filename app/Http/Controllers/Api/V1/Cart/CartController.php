<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Cart;

use App\Domain\Cart\Services\CartService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Cart\CartItemRequest;
use App\Http\Requests\V1\Cart\UpdateCartItemRequest;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    protected CartService $cartService;

    /**
     * Create a new controller instance.
     *
     * @param CartService $cartService
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the current user's cart.
     *
     * GET /api/cart
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $cart = $this->cartService->getCartWithDetails($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'cart' => $cart,
                    'items' => $cart->items,
                    'total' => number_format($cart->total, 2, '.', ''),
                    'items_count' => $cart->total_items,
                ],
                'message' => 'Cart retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving cart: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add an item to the cart.
     *
     * POST /api/cart/items
     *
     * @param CartItemRequest $request
     * @return JsonResponse
     */
    public function addItem(CartItemRequest $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $cartItem = $this->cartService->addItem(
                $userId,
                $request->product_id,
                $request->getQuantity()
            );

            return response()->json([
                'success' => true,
                'data' => $cartItem,
                'message' => 'Item added to cart successfully',
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Product not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error adding item to cart: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the quantity of a cart item.
     *
     * PUT/PATCH /api/cart/items/{id}
     *
     * @param UpdateCartItemRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateItem(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $cartItem = $this->cartService->updateItemQuantity(
                $userId,
                $id,
                $request->quantity
            );

            return response()->json([
                'success' => true,
                'data' => $cartItem,
                'message' => 'Cart item updated successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Cart item not found',
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error updating cart item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove an item from the cart.
     *
     * DELETE /api/cart/items/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function removeItem(int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $this->cartService->removeItem($userId, $id);

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Item removed from cart successfully',
            ], 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Cart item not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error removing item from cart: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all items from the cart.
     *
     * DELETE /api/cart
     *
     * @return JsonResponse
     */
    public function clear(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $this->cartService->clearCart($userId);

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Cart cleared successfully',
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error clearing cart: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cart total.
     *
     * GET /api/cart/total
     *
     * @return JsonResponse
     */
    public function total(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $totals = $this->cartService->getCartTotals($userId);

            return response()->json([
                'success' => true,
                'data' => $totals,
                'message' => 'Cart total retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving cart total: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply a coupon to the cart.
     *
     * POST /api/cart/coupon
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function applyCoupon(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            // TODO: Implement coupon logic when discount system is ready
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Coupon functionality not yet implemented',
            ], 501);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error applying coupon: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a coupon from the cart.
     *
     * DELETE /api/cart/coupon
     *
     * @return JsonResponse
     */
    public function removeCoupon(): JsonResponse
    {
        try {
            // TODO: Implement coupon logic when discount system is ready
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Coupon functionality not yet implemented',
            ], 501);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error removing coupon: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cart summary with totals.
     *
     * GET /api/cart/summary
     *
     * @return JsonResponse
     */
    public function summary(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $cart = $this->cartService->getCartWithDetails($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => number_format($cart->total, 2, '.', ''),
                    'items_count' => $cart->total_items,
                    'items' => $cart->items,
                ],
                'message' => 'Cart summary retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving cart summary: ' . $e->getMessage(),
            ], 500);
        }
    }
}
