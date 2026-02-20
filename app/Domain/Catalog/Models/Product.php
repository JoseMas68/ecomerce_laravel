<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string|null $description
 * @property float $price
 * @property float|null $compare_at_price
 * @property float|null $cost
 * @property int $stock
 * @property int $brand_id
 * @property int $category_id
 * @property bool $is_active
 * @property string|null $image_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Brand $brand
 * @property-read Category $category
 */
class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'compare_at_price',
        'cost',
        'stock',
        'brand_id',
        'category_id',
        'is_active',
        'image_url',
    ];

    protected $casts = [
        'price' => 'float',
        'compare_at_price' => 'float',
        'cost' => 'float',
        'stock' => 'integer',
        'brand_id' => 'integer',
        'category_id' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the brand that owns the product.
     *
     * @return BelongsTo<Brand, Product>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the category that owns the product.
     *
     * @return BelongsTo<Category, Product>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the stock status of the product.
     *
     * @return string
     */
    public function getStockStatus(): string
    {
        if ($this->stock === 0) {
            return 'out_of_stock';
        } elseif ($this->stock <= 10) {
            return 'low_stock';
        }

        return 'in_stock';
    }
}
