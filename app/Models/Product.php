<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Product con scopes de optimización de queries.
 *
 * Los scopes permiten cargar relaciones de manera anticipada (eager loading)
 * para evitar el problema N+1 y reducir el número de queries a la base de datos.
 *
 * Uso recomendado:
 * - Product::withCategoryAndBrand()->get() para listados
 * - Product::withReviews()->find($id) para detalle
 * - Product::catalogList()->paginate(12) para catálogo paginado
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'price',
        'compare_price',
        'cost',
        'stock',
        'low_stock_threshold',
        'weight',
        'category_id',
        'brand_id',
        'active',
        'featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * Atributos que deben ser casteados.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock' => 'integer',
        'active' => 'boolean',
        'featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con categoría (N:1).
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relación con marca (N:1).
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Relación con imágenes (1:N).
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    /**
     * Relación con reviews (1:N).
     */
    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }

    /**
     * Relación con items de orden (N:M).
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope para cargar categoría y marca eager loading.
     *
     * Optimiza queries para listados de productos evitando N+1.
     * Carga anticipada: category, brand
     *
     * Uso: Product::withCategoryAndBrand()->paginate(12)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCategoryAndBrand($query)
    {
        return $query->with(['category:id,name,slug', 'brand:id,name,slug']);
    }

    /**
     * Scope para cargar reviews con eager loading.
     *
     * Optimiza queries para detalle de producto.
     * Carga anticipada: reviews con usuario aprobado
     *
     * Uso: Product::withReviews()->find($id)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithReviews($query)
    {
        return $query->with(['reviews' => function ($query) {
            $query->where('approved', true)
                ->with('user:id,name,avatar');
        }]);
    }

    /**
     * Scope para listado completo de catálogo.
     *
     * Combina las relaciones más usadas en catálogo.
     * Carga anticipada: category, brand, images (primera)
     *
     * Uso: Product::catalogList()->paginate(12)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCatalogList($query)
    {
        return $query->with(['category:id,name,slug', 'brand:id,name,slug'])
            ->with(['images' => function ($query) {
                $query->orderBy('order')->limit(1);
            }]);
    }

    /**
     * Scope para productos activos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para productos destacados.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope para productos con stock disponible.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope para productos con bajo stock.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('stock', '>', 0);
    }

    /**
     * Scope para productos sin stock.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    /**
     * Scope para productos en oferta.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'price');
    }

    /**
     * Scope para productos de una categoría por slug.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategorySlug($query, string $slug)
    {
        return $query->whereHas('category', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

    /**
     * Scope para productos de una marca por slug.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByBrandSlug($query, string $slug)
    {
        return $query->whereHas('brand', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

    /**
     * Scope para búsqueda de productos.
     *
     * Busca en nombre, descripción y SKU.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    /**
     * Calcula el rating promedio del producto.
     *
     * @return float
     */
    public function getAverageRatingAttribute(): float
    {
        return (float) $this->reviews()
            ->where('approved', true)
            ->avg('rating') ?? 0;
    }

    /**
     * Obtiene el precio de oferta si existe, sino el precio normal.
     *
     * @return float
     */
    public function getFinalPriceAttribute(): float
    {
        return $this->compare_price && $this->compare_price > $this->price
            ? $this->compare_price
            : $this->price;
    }

    /**
     * Calcula el porcentaje de descuento.
     *
     * @return int|null
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
        }
        return null;
    }

    /**
     * Verifica si el producto tiene bajo stock.
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold && $this->stock > 0;
    }

    /**
     * Verifica si el producto está sin stock.
     *
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    /**
     * Verifica si el producto está en oferta.
     *
     * @return bool
     */
    public function isOnSale(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    /**
     * Boot del modelo con eventos para limpiar cache.
     */
    protected static function booted()
    {
        // Limpiar cache al actualizar producto
        static::updated(function ($product) {
            if (app()->bound(\App\Domain\Catalog\Services\CatalogCacheService::class)) {
                app(\App\Domain\Catalog\Services\CatalogCacheService::class)
                    ->clearProductCache($product->id);
            }
        });

        // Limpiar cache al eliminar producto
        static::deleted(function ($product) {
            if (app()->bound(\App\Domain\Catalog\Services\CatalogCacheService::class)) {
                app(\App\Domain\Catalog\Services\CatalogCacheService::class)
                    ->clearProductCache($product->id);
            }
        });
    }
}
