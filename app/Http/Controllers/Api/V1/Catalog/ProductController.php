<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Domain\Catalog\Actions\Product\CreateProductAction;
use App\Domain\Catalog\Actions\Product\DeleteProductAction;
use App\Domain\Catalog\Actions\Product\UpdateProductAction;
use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Catalog\StoreProductRequest;
use App\Http\Requests\V1\Catalog\UpdateProductRequest;
use App\Http\Resources\Catalog\ProductCollection;
use App\Http\Resources\Catalog\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for Product CRUD operations
 */
class ProductController extends Controller
{
    /**
     * Display a listing of products.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Cache de 5 minutos para listados de productos
            $cacheKey = 'products:index:' . md5(json_encode($request->all()));
            
            return \Illuminate\Support\Facades\Cache::remember(
                $cacheKey, 
                now()->addMinutes(5),
                function () use ($request) {
                    $query = Product::query()->with(['brand', 'category']);
        
                    // Filter by brand_id if provided
                    if ($request->has('brand_id')) {
                        $query->where('brand_id', $request->input('brand_id'));
                    }
        
                    // Filter by category_id if provided
                    if ($request->has('category_id')) {
                        $query->where('category_id', $request->input('category_id'));
                    }
        
                    // Filter by is_active if provided
                    if ($request->has('is_active')) {
                        $query->where('is_active', $request->boolean('is_active'));
                    }
        
                    // Filter by min_price if provided
                    if ($request->has('min_price')) {
                        $query->where('price', '>=', (float) $request->input('min_price'));
                    }
        
                    // Filter by max_price if provided
                    if ($request->has('max_price')) {
                        $query->where('price', '<=', (float) $request->input('max_price'));
                    }
        
                    // Search by name, sku, or description
                    if ($request->has('search')) {
                        $searchTerm = $request->input('search');
                        $query->where(function ($q) use ($searchTerm) {
                            $q->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('sku', 'like', "%{$searchTerm}%")
                                ->orWhere('description', 'like', "%{$searchTerm}%");
                        });
                    }
        
                    return $query->orderBy('name')->paginate(15);
                }
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving products: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified product.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Cache de 10 minutos para producto individual
            $cacheKey = 'products:show:' . $id;
            
            $product = \Illuminate\Support\Facades\Cache::remember(
                $cacheKey,
                now()->addMinutes(10),
                fn() => Product::with(['brand', 'category'])->findOrFail($id)
            );

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product),
                'message' => 'Product retrieved successfully',
            ], 200);
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
                'message' => 'Error retrieving product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created product in storage.
     *
     * @param StoreProductRequest $request
     * @param CreateProductAction $action
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request, CreateProductAction $action): JsonResponse
    {
        try {
            $data = \App\Domain\Catalog\DTOs\CreateProductData::fromRequest($request);
            $product = $action($data);

            // Invalidar caché de productos
            \Illuminate\Support\Facades\Cache::tags(['products'])->flush();

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product),
                'message' => 'Product created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error creating product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product in storage.
     *
     * @param UpdateProductRequest $request
     * @param int $id
     * @param UpdateProductAction $action
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, int $id, UpdateProductAction $action): JsonResponse
    {
        try {
            $data = \App\Domain\Catalog\DTOs\UpdateProductData::fromRequest($request);
            $product = $action($id, $data);

            // Invalidar caché del producto específico y listados
            \Illuminate\Support\Facades\Cache::forget('products:show:' . $id);
            \Illuminate\Support\Facades\Cache::tags(['products'])->flush();

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product),
                'message' => 'Product updated successfully',
            ], 200);
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
                'message' => 'Error updating product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product from storage (soft delete).
     *
     * @param int $id
     * @param DeleteProductAction $action
     * @return JsonResponse
     */
    public function destroy(int $id, DeleteProductAction $action): JsonResponse
    {
        try {
            $action($id);

            // Invalidar caché del producto eliminado y listados
            \Illuminate\Support\Facades\Cache::forget('products:show:' . $id);
            \Illuminate\Support\Facades\Cache::tags(['products'])->flush();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Product deleted successfully',
            ], 204);
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
                'message' => 'Error deleting product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search products by query.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->input('query', '');

            if (empty($searchTerm)) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Search query is required',
                ], 422);
            }

            $products = Product::with(['brand', 'category'])
                ->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('sku', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%")
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => new ProductCollection($products),
                'message' => 'Search results retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error searching products: ' . $e->getMessage(),
            ], 500);
        }
    }
}
