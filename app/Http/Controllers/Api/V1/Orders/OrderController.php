<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Orders;

use App\Domain\Cart\Services\CartService;
use App\Domain\Orders\Services\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Orders\CreateOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected CartService $cartService;

    /**
     * Create a new controller instance.
     *
     * @param OrderService $orderService
     * @param CartService $cartService
     */
    public function __construct(OrderService $orderService, CartService $cartService)
    {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
    }

    /**
     * Create a new order from the user's cart.
     *
     * POST /api/v1/orders
     *
     * @param CreateOrderRequest $request
     * @return JsonResponse
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $userId = auth()->id();

            $cart = $this->cartService->getCartWithDetails($userId);

            if ($cart->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Cannot create order: cart is empty',
                ], 400);
            }

            $unavailableItems = $this->cartService->validateCartStock($cart);
            if (!empty($unavailableItems)) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'unavailable_items' => $unavailableItems,
                    ],
                    'message' => 'Cannot create order: some items are out of stock',
                ], 400);
            }

            $shippingData = [
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'shipping_method' => $request->shipping_method,
                'notes' => $request->notes,
            ];

            $order = $this->orderService->createOrderFromCart(
                $cart,
                $shippingData,
                $request->payment_method
            );

            $order->load(['items', 'user']);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'order_number' => $order->order_number,
                    'total' => number_format($order->total, 2, '.', ''),
                    'items_count' => $order->items->count(),
                ],
                'message' => 'Order created successfully',
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Resource not found: ' . $e->getMessage(),
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error creating order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List orders for the authenticated user.
     *
     * GET /api/v1/orders
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $status = $request->query('status');
            $perPage = (int) $request->query('per_page', 15);
            $page = (int) $request->query('page', 1);

            if ($perPage < 1 || $perPage > 100) {
                $perPage = 15;
            }

            $orders = $this->orderService->getUserOrders($userId, $status, $perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orders->items(),
                    'pagination' => [
                        'total' => $orders->total(),
                        'per_page' => $orders->perPage(),
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage(),
                        'from' => $orders->firstItem(),
                        'to' => $orders->lastItem(),
                    ],
                ],
                'message' => 'Orders retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving orders: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get details of a specific order.
     *
     * GET /api/v1/orders/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $order = $this->orderService->getOrderById($id, $userId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Order not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'items' => $order->items,
                    'payments' => $order->payments,
                    'shipping_address' => $order->shipping_address,
                    'billing_address' => $order->billing_address,
                    'status_label' => $order->status_label,
                ],
                'message' => 'Order retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order statistics for the authenticated user.
     *
     * GET /api/v1/orders/stats
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $stats = $this->orderService->getOrderStats($userId);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Order statistics retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving order statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an order.
     *
     * PATCH /api/v1/orders/{id}/cancel
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $reason = $request->input('reason');

            $order = $this->orderService->getOrderById($id, $userId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Order not found',
                ], 404);
            }

            if ($order->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'You do not have permission to cancel this order',
                ], 403);
            }

            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'current_status' => $order->status->value,
                        'status_label' => $order->status_label,
                    ],
                    'message' => 'Order cannot be cancelled. Only pending or processing orders can be cancelled.',
                ], 422);
            }

            $this->orderService->cancelOrder($id, $userId, $reason);

            $order->refresh();
            $order->load(['items', 'payments']);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'status_label' => $order->status_label,
                ],
                'message' => 'Order cancelled successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Order not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error cancelling order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get tracking information for an order.
     *
     * GET /api/v1/orders/{id}/track
     *
     * @param int $id
     * @return JsonResponse
     */
    public function track(int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $order = $this->orderService->getOrderById($id, $userId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Order not found',
                ], 404);
            }

            if ($order->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'You do not have permission to view this order',
                ], 403);
            }

            $estimatedDelivery = null;
            if ($order->shipped_at) {
                $estimatedDelivery = $order->shipped_at->addDays(3)->toIso8601String();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status->value,
                    'status_label' => $order->status_label,
                    'tracking_number' => $order->tracking_number ?? null,
                    'shipping_carrier' => $order->shipping_carrier ?? null,
                    'shipping_method' => $order->shipping_method,
                    'estimated_delivery' => $estimatedDelivery,
                    'shipped_at' => $order->shipped_at?->toIso8601String(),
                    'delivered_at' => $order->delivered_at?->toIso8601String(),
                ],
                'message' => 'Tracking information retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving tracking information: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request a refund for an order.
     *
     * POST /api/v1/orders/{id}/refund
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function refund(Request $request, int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $reason = $request->input('reason', 'User requested refund');

            $order = $this->orderService->getOrderById($id, $userId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Order not found',
                ], 404);
            }

            if ($order->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'You do not have permission to refund this order',
                ], 403);
            }

            if (!$order->canBeRefunded()) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'current_status' => $order->status->value,
                        'payment_status' => $order->payment_status->value,
                    ],
                    'message' => 'Order cannot be refunded. Only delivered or shipped orders with completed payment can be refunded.',
                ], 422);
            }

            $this->orderService->updateOrderStatus($id, 'refunded', $reason);

            $order->refresh();
            $order->load(['items', 'payments']);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'status_label' => $order->status_label,
                ],
                'message' => 'Refund requested successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Order not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error processing refund: ' . $e->getMessage(),
            ], 500);
        }
    }
}
