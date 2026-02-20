<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    /**
     * Display the specified product.
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->with(['brand', 'category'])
            ->firstOrFail();

        // Productos relacionados de la misma categoría
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        return Inertia::render('Catalog/Show', [
            'product' => $product,
            'related_products' => $relatedProducts,
        ]);
    }
}
