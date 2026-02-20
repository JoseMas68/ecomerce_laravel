<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Domain\Catalog\Actions\Category\CreateCategoryAction;
use App\Domain\Catalog\Actions\Category\DeleteCategoryAction;
use App\Domain\Catalog\Actions\Category\UpdateCategoryAction;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Services\CategoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Catalog\StoreCategoryRequest;
use App\Http\Requests\V1\Catalog\UpdateCategoryRequest;
use App\Http\Resources\Catalog\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for Category CRUD operations
 */
class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Category::query();

            // Filter by is_active if provided
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Filter by parent_id if provided
            if ($request->has('parent_id')) {
                $query->where('parent_id', $request->input('parent_id'));
            }

            $categories = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => CategoryResource::collection($categories),
                'message' => 'Categories retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving categories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified category.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = Category::with(['parent', 'children'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new CategoryResource($category),
                'message' => 'Category retrieved successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created category in storage.
     *
     * @param StoreCategoryRequest $request
     * @param CreateCategoryAction $action
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request, CreateCategoryAction $action): JsonResponse
    {
        try {
            $data = \App\Domain\Catalog\DTOs\CreateCategoryData::fromRequest($request);
            $category = $action($data);

            return response()->json([
                'success' => true,
                'data' => new CategoryResource($category),
                'message' => 'Category created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error creating category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified category in storage.
     *
     * @param UpdateCategoryRequest $request
     * @param int $id
     * @param UpdateCategoryAction $action
     * @return JsonResponse
     */
    public function update(UpdateCategoryRequest $request, int $id, UpdateCategoryAction $action): JsonResponse
    {
        try {
            $data = \App\Domain\Catalog\DTOs\UpdateCategoryData::fromRequest($request);
            $category = $action($id, $data);

            return response()->json([
                'success' => true,
                'data' => new CategoryResource($category),
                'message' => 'Category updated successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error updating category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified category from storage (soft delete).
     *
     * @param int $id
     * @param DeleteCategoryAction $action
     * @return JsonResponse
     */
    public function destroy(int $id, DeleteCategoryAction $action): JsonResponse
    {
        try {
            $action($id);

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Category deleted successfully',
            ], 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Category not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error deleting category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the complete category tree.
     *
     * @param CategoryService $service
     * @return JsonResponse
     */
    public function tree(CategoryService $service): JsonResponse
    {
        try {
            $tree = $service->getCategoryTree();

            return response()->json([
                'success' => true,
                'data' => CategoryResource::collection($tree),
                'message' => 'Category tree retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Error retrieving category tree: ' . $e->getMessage(),
            ], 500);
        }
    }
}
