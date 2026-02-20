<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Domain\Catalog\Actions\Brand\CreateBrandAction;
use App\Domain\Catalog\Actions\Brand\DeleteBrandAction;
use App\Domain\Catalog\Actions\Brand\UpdateBrandAction;
use App\Domain\Catalog\Models\Brand;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Catalog\StoreBrandRequest;
use App\Http\Requests\V1\Catalog\UpdateBrandRequest;
use App\Http\Resources\Catalog\BrandResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for Brand CRUD operations
 */
class BrandController extends Controller
{
    /**
     * Display a listing of brands.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Brand::query();

            // Filter by is_active if provided
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $brands = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => BrandResource::collection($brands),
                'message' => 'Brands retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving brands: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified brand.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $brand = Brand::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new BrandResource($brand),
                'message' => 'Brand retrieved successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Brand not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving brand: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created brand in storage.
     *
     * @param StoreBrandRequest $request
     * @param CreateBrandAction $action
     * @return JsonResponse
     */
    public function store(StoreBrandRequest $request, CreateBrandAction $action): JsonResponse
    {
        try {
            $data = \App\Domain\Catalog\DTOs\CreateBrandData::fromRequest($request);
            $brand = $action($data);

            return response()->json([
                'success' => true,
                'data' => new BrandResource($brand),
                'message' => 'Brand created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error creating brand: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified brand in storage.
     *
     * @param UpdateBrandRequest $request
     * @param int $id
     * @param UpdateBrandAction $action
     * @return JsonResponse
     */
    public function update(UpdateBrandRequest $request, int $id, UpdateBrandAction $action): JsonResponse
    {
        try {
            $data = \App\Domain\Catalog\DTOs\UpdateBrandData::fromRequest($request);
            $brand = $action($id, $data);

            return response()->json([
                'success' => true,
                'data' => new BrandResource($brand),
                'message' => 'Brand updated successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Brand not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error updating brand: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified brand from storage (soft delete).
     *
     * @param int $id
     * @param DeleteBrandAction $action
     * @return JsonResponse
     */
    public function destroy(int $id, DeleteBrandAction $action): JsonResponse
    {
        try {
            $action($id);

            return response()->json(null, 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Brand not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error deleting brand: ' . $e->getMessage(),
            ], 500);
        }
    }
}
