<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domain\Catalog\Models\Product;

echo "=== VERIFICACIÓN DE IMÁGENES DE PRODUCTOS ===\n\n";

$products = Product::select('id', 'name', 'image_url')->get();

echo "Total de productos: " . $products->count() . "\n\n";

foreach ($products as $product) {
    echo "ID: {$product->id}\n";
    echo "Nombre: {$product->name}\n";
    echo "Imagen URL: {$product->image_url}\n";

    if ($product->image_url) {
        // Verificar si es de Unsplash
        $isUnsplash = str_contains($product->image_url, 'unsplash.com');
        echo "Fuente: " . ($isUnsplash ? 'Unsplash' : 'Otra') . "\n";

        // Verificar si la URL existe (haciendo un HEAD request)
        $ch = curl_init($product->image_url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "Estado HTTP: {$httpCode} " . ($httpCode === 200 ? '✓ OK' : '✗ ERROR') . "\n";
    } else {
        echo "Estado: Sin imagen (usará placeholder)\n";
    }

    echo str_repeat("-", 60) . "\n";
}

echo "\n=== ANÁLISIS COMPLETO ===\n";
$withImages = $products->filter(fn($p) => !empty($p->image_url))->count();
$withoutImages = $products->filter(fn($p) => empty($p->image_url))->count();
echo "Con imagen: {$withImages}\n";
echo "Sin imagen: {$withoutImages}\n";
