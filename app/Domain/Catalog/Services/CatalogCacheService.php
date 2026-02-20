<?php

namespace App\Domain\Catalog\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de caché para optimizar el rendimiento del catálogo.
 *
 * Este servicio implementa una capa de caché Redis para reducir la carga
 * en la base de datos y mejorar los tiempos de respuesta del catálogo.
 *
 * Estrategias de cache:
 * - Productos por categoría: 24 horas (se invalida al actualizar productos)
 * - Producto individual: 6 horas (contenido más dinámico)
 * - Categorías: 24 horas (cambios poco frecuentes)
 * - Marcas: 24 horas (cambios poco frecuentes)
 * - Productos destacados: 1 hora (alta rotación)
 * - Búsquedas populares: 12 horas (tendencias medias)
 */
class CatalogCacheService
{
    /**
     * Prefijo para todas las claves de caché del catálogo.
     * Permite identificar y limpiar fácilmente todas las claves relacionadas.
     */
    private string $cachePrefix = 'catalog_';

    /**
     * Tiempos de cache en segundos.
     * Constantes para mantener consistencia y facilitar ajustes.
     */
    private int $productsTtl = 86400;      // 24 horas
    private int $productTtl = 21600;       // 6 horas
    private int $categoriesTtl = 86400;    // 24 horas
    private int $brandsTtl = 86400;        // 24 horas
    private int $featuredTtl = 3600;       // 1 hora
    private int $searchTtl = 43200;        // 12 horas

    /**
     * Obtiene lista de productos por categoría con caché.
     *
     * Cache Key: catalog_products_category_{category_slug}_{page}_{perPage}
     * TTL: 24 horas
     *
     * @param string $categorySlug Slug de la categoría
     * @param int $perPage Productos por página (default: 12)
     * @param int $page Número de página (default: 1)
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function rememberProducts(string $categorySlug, int $perPage = 12, int $page = 1)
    {
        $cacheKey = $this->getCacheKey("products_category_{$categorySlug}_{$page}_{$perPage}");

        return Cache::remember($cacheKey, $this->productsTtl, function () use ($categorySlug, $perPage) {
            Log::info('Cache miss: Products by category', ['category' => $categorySlug]);

            return Product::withCategoryAndBrand()
                ->where('active', true)
                ->whereHas('category', function ($query) use ($categorySlug) {
                    $query->where('slug', $categorySlug);
                })
                ->orderBy('featured', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
    }

    /**
     * Obtiene un producto individual con todas sus relaciones.
     *
     * Cache Key: catalog_product_{id}
     * TTL: 6 horas (menor tiempo por ser más dinámico: stock, reviews, etc.)
     *
     * @param int $productId ID del producto
     * @return Product|null
     */
    public function rememberProduct(int $productId)
    {
        $cacheKey = $this->getCacheKey("product_{$productId}");

        return Cache::remember($cacheKey, $this->productTtl, function () use ($productId) {
            Log::info('Cache miss: Product detail', ['product_id' => $productId]);

            return Product::withCategoryAndBrand()
                ->withReviews()
                ->with(['images' => function ($query) {
                    $query->orderBy('order');
                }])
                ->find($productId);
        });
    }

    /**
     * Obtiene lista de todas las categorías activas.
     *
     * Cache Key: catalog_categories_all
     * TTL: 24 horas
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function rememberCategories()
    {
        $cacheKey = $this->getCacheKey('categories_all');

        return Cache::remember($cacheKey, $this->categoriesTtl, function () {
            Log::info('Cache miss: Categories list');

            return Category::active()
                ->withCount('products')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Obtiene lista de todas las marcas activas.
     *
     * Cache Key: catalog_brands_all
     * TTL: 24 horas
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function rememberBrands()
    {
        $cacheKey = $this->getCacheKey('brands_all');

        return Cache::remember($cacheKey, $this->brandsTtl, function () {
            Log::info('Cache miss: Brands list');

            return Brand::active()
                ->withCount('products')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Obtiene productos destacados en home.
     *
     * Cache Key: catalog_featured_products
     * TTL: 1 hora (alta rotación, cambios frecuentes)
     *
     * @param int $limit Número de productos a retornar
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function rememberFeaturedProducts(int $limit = 12)
    {
        $cacheKey = $this->getCacheKey("featured_products_{$limit}");

        return Cache::remember($cacheKey, $this->featuredTtl, function () use ($limit) {
            Log::info('Cache miss: Featured products');

            return Product::withCategoryAndBrand()
                ->where('active', true)
                ->where('featured', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Obtiene resultados de búsqueda populares con caché.
     *
     * Cache Key: catalog_search_{query}_{page}_{perPage}
     * TTL: 12 horas
     *
     * @param string $query Término de búsqueda
     * @param int $perPage Resultados por página
     * @param int $page Número de página
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function rememberSearchResults(string $query, int $perPage = 12, int $page = 1)
    {
        $cacheKey = $this->getCacheKey("search_{$query}_{$page}_{$perPage}");

        return Cache::remember($cacheKey, $this->searchTtl, function () use ($query, $perPage) {
            Log::info('Cache miss: Search results', ['query' => $query]);

            return Product::withCategoryAndBrand()
                ->where('active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('sku', 'like', "%{$query}%");
                })
                ->orderBy('featured', 'desc')
                ->orderBy('name')
                ->paginate($perPage);
        });
    }

    /**
     * Obtiene productos relacionados basados en categoría.
     *
     * Cache Key: catalog_related_{productId}_{limit}
     * TTL: 6 horas
     *
     * @param int $productId ID del producto de referencia
     * @param int $limit Número de productos relacionados
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function rememberRelatedProducts(int $productId, int $limit = 4)
    {
        $cacheKey = $this->getCacheKey("related_{$productId}_{$limit}");

        return Cache::remember($cacheKey, $this->productTtl, function () use ($productId, $limit) {
            Log::info('Cache miss: Related products', ['product_id' => $productId]);

            $product = Product::find($productId);

            if (!$product) {
                return collect();
            }

            return Product::withCategoryAndBrand()
                ->where('active', true)
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $productId)
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Limpia el caché de un producto específico.
     * Se debe llamar al actualizar o eliminar un producto.
     *
     * @param int $productId ID del producto
     * @return void
     */
    public function clearProductCache(int $productId): void
    {
        $product = Product::find($productId);

        if ($product) {
            // Limpiar caché del producto individual
            Cache::forget($this->getCacheKey("product_{$productId}"));

            // Limpiar caché de productos relacionados
            Cache::forget($this->getCacheKey("related_{$productId}_4"));
            Cache::forget($this->getCacheKey("related_{$productId}_8"));

            // Limpiar caché de la categoría del producto
            if ($product->category) {
                $this->clearCategoryCache($product->category_id);
            }

            // Limpiar caché de featured (podría estar incluido)
            for ($i = 1; $i <= 24; $i++) {
                Cache::forget($this->getCacheKey("featured_products_{$i}"));
            }

            Log::info('Product cache cleared', ['product_id' => $productId]);
        }
    }

    /**
     * Limpia el caché de una categoría específica.
     * Se debe llamar al actualizar o eliminar una categoría.
     *
     * @param int $categoryId ID de la categoría
     * @return void
     */
    public function clearCategoryCache(int $categoryId): void
    {
        $category = Category::find($categoryId);

        if ($category) {
            // Limpiar todas las páginas de productos de esta categoría
            for ($page = 1; $page <= 50; $page++) {
                for ($perPage = 12; $perPage <= 48; $perPage += 12) {
                    Cache::forget($this->getCacheKey("products_category_{$category->slug}_{$page}_{$perPage}"));
                }
            }

            Log::info('Category cache cleared', ['category_id' => $categoryId]);
        }
    }

    /**
     * Limpia todo el caché del catálogo.
     * Útil para deployments o cambios masivos.
     *
     * @return void
     */
    public function clearAllCatalogCache(): void
    {
        // Obtener todas las claves que coinciden con el prefijo
        $redis = Cache::getRedis();
        $keys = $redis->keys("{$this->cachePrefix}*");

        if (!empty($keys)) {
            $redis->del($keys);
        }

        Log::info('All catalog cache cleared', ['keys_count' => count($keys)]);
    }

    /**
     * Precarga el caché del catálogo.
     * Útil para "calentar" el caché después de un deployment.
     *
     * @return array Estadísticas de la precarga
     */
    public function warmup(): array
    {
        $stats = [
            'categories' => 0,
            'brands' => 0,
            'featured' => 0,
            'errors' => [],
        ];

        try {
            // Precargar categorías
            $this->rememberCategories();
            $stats['categories'] = 1;

            // Precargar marcas
            $this->rememberBrands();
            $stats['brands'] = 1;

            // Precargar productos destacados
            $this->rememberFeaturedProducts();
            $stats['featured'] = 1;

            Log::info('Catalog cache warmup completed', $stats);
        } catch (\Exception $e) {
            $stats['errors'][] = $e->getMessage();
            Log::error('Catalog cache warmup failed', ['error' => $e->getMessage()]);
        }

        return $stats;
    }

    /**
     * Obtiene estadísticas del caché del catálogo.
     *
     * @return array Información sobre el estado del caché
     */
    public function getCacheStats(): array
    {
        $redis = Cache::getRedis();
        $keys = $redis->keys("{$this->cachePrefix}*");

        return [
            'total_keys' => count($keys),
            'prefix' => $this->cachePrefix,
            'memory_usage' => $redis->info('memory'),
        ];
    }

    /**
     * Genera una clave de caché con el prefijo configurado.
     *
     * @param string $key Clave sin prefijo
     * @return string Clave completa con prefijo
     */
    private function getCacheKey(string $key): string
    {
        return $this->cachePrefix . $key;
    }
}
