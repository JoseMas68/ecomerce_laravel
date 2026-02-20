<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $royalCanin = Brand::where('slug', 'royal-canin')->first();
        $purina = Brand::where('slug', 'purina-pro-plan')->first();
        $kong = Brand::where('slug', 'kong')->first();
        $nylabone = Brand::where('slug', 'nylabone')->first();
        $frisco = Brand::where('slug', 'frisco')->first();
        $whiskas = Brand::where('slug', 'whiskas')->first();

        $alimentoPerros = Category::where('slug', 'alimento-perros')->first();
        $alimentoGatos = Category::where('slug', 'alimento-gatos')->first();
        $juguetes = Category::where('slug', 'juguetes')->first();
        $camas = Category::where('slug', 'camas-y-descanso')->first();

        $products = [
            // Alimento para Perros
            [
                'name' => 'Royal Canin Adulto Grande - 15kg',
                'slug' => 'royal-canin-adulto-grande-15kg',
                'description' => 'Alimento completo para perros adultos de razas grandes (26-44kg). Formula adaptada para satisfacer las necesidades nutricionales de perros grandes. Ayuda a mantener una masa muscular ideal y soporta la salud ósea y articular.',
                'price' => 89.99,
                'compare_at_price' => 105.00,
                'sku' => 'RC-ADULT-LARGE-15',
                'stock' => 45,
                'brand_id' => $royalCanin->id,
                'category_id' => $alimentoPerros->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1625316708582-7c38734be31d?w=800&auto=format&fit=crop',
            ],
            [
                'name' => 'Purina Pro Plan Cachorro - 3kg',
                'slug' => 'purina-pro-plan-cachorro-3kg',
                'description' => 'Alimento premium para cachorros con DHA natural para el desarrollo cerebral. Proteína de alta calidad para ayudar a construir músculos fuertes y saludables. antioxidantes para apoyar el sistema inmunológico.',
                'price' => 24.99,
                'compare_at_price' => null,
                'sku' => 'PP-PUPPY-3',
                'stock' => 78,
                'brand_id' => $purina->id,
                'category_id' => $alimentoPerros->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1589924691195-414898874467?w=800&auto=format&fit=crop',
            ],
            [
                'name' => 'Royal Canin Mini Adulto - 8kg',
                'slug' => 'royal-canin-mini-adulto-8kg',
                'description' => 'Nutrición específica para perros adultos mini (de 1 a 10kg). Formula adaptada que ayuda a mantener el peso ideal y soporta la salud digestiva con proteínas de alta digestibilidad.',
                'price' => 54.99,
                'compare_at_price' => 65.00,
                'sku' => 'RC-MINI-ADULT-8',
                'stock' => 32,
                'brand_id' => $royalCanin->id,
                'category_id' => $alimentoPerros->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1623387641168-d9803ddd3f35?w=800&auto=format&fit=crop',
            ],
            // Alimento para Gatos
            [
                'name' => 'Whiskas Pescado y Mariscos - 10kg',
                'slug' => 'whiskas-pescado-mariscos-10kg',
                'description' => 'Alimento completo para gatos adultos con deliciosos trocitos en salsa real de pescado y mariscos. 100% completo y balanceado, sin colorantes artificiales ni sabores sintéticos.',
                'price' => 38.99,
                'compare_at_price' => null,
                'sku' => 'WH-FISH-10',
                'stock' => 56,
                'brand_id' => $whiskas->id,
                'category_id' => $alimentoGatos->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=800&auto=format&fit=crop',
            ],
            [
                'name' => 'Royal Canin Kitten - 4kg',
                'slug' => 'royal-canin-kitten-4kg',
                'description' => 'Alimento para gatitos de 4 a 12 meses. Apoya el segundo crecimiento del gatito y fortalece el sistema inmunológico. Digestibilidad alta con proteínas L.I.P.',
                'price' => 32.99,
                'compare_at_price' => 39.99,
                'sku' => 'RC-KITTEN-4',
                'stock' => 41,
                'brand_id' => $royalCanin->id,
                'category_id' => $alimentoGatos->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800&auto=format&fit=crop',
            ],
            // Juguetes
            [
                'name' => 'KONG Classic - Tamaño Mediano',
                'slug' => 'kong-classic-mediano',
                'description' => 'El juguete de caucho natural más duradero del mundo. Perfecto para rellenar con premios y mantener a tu perro entretenido durante horas. Ideal para perros de 13-35kg.',
                'price' => 18.99,
                'compare_at_price' => null,
                'sku' => 'KONG-CL-MED',
                'stock' => 124,
                'brand_id' => $kong->id,
                'category_id' => $juguetes->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1595528174097-a87a49904ccf?w=800&auto=format&fit=crop',
            ],
            [
                'name' => 'Nylabone Dura Chew - Menta',
                'slug' => 'nylabone-dura-chew-menta',
                'description' => 'Masticable duradero con sabor a menta refrescante. Ayuda a limpiar los dientes mientras tu perro juega. Textura dura para masticadores fuertes.',
                'price' => 14.99,
                'compare_at_price' => null,
                'sku' => 'NB-DC-MINT',
                'stock' => 87,
                'brand_id' => $nylabone->id,
                'category_id' => $juguetes->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1535294445180-452810865567?w=800&auto=format&fit=crop',
            ],
            [
                'name' => 'KONG Wubba - Juguete Interactivo',
                'slug' => 'kong-wubba-interactivo',
                'description' => 'Juguete interactivo con cola aterrizadora. Perfecto para lanzar y traer. Fabricado con materiales duraderos y colores vibrantes para estimular el juego.',
                'price' => 12.99,
                'compare_at_price' => null,
                'sku' => 'KONG-WUBBA',
                'stock' => 95,
                'brand_id' => $kong->id,
                'category_id' => $juguetes->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1535930891776-0c2dfb7f2e5f?w=800&auto=format&fit=crop',
            ],
            // Camas
            [
                'name' => 'Frisco Rectangular Bolster Dog Bed - 120x80cm',
                'slug' => 'frisco-cama-rectangular-perro',
                'description' => 'Cama rectangular con borde acolchado para perros. Tela suave y duradera con relleno de espuma viscoelástica de alta densidad. Funda lavable y con cremallera.',
                'price' => 79.99,
                'compare_at_price' => 99.99,
                'sku' => 'FR-BED-REC-120',
                'stock' => 23,
                'brand_id' => $frisco->id,
                'category_id' => $camas->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1600369692736-345e52d87688?w=800&auto=format&fit=crop',
            ],
            [
                'name' => 'Frisco Cat Cave - Cama Cueva para Gatos',
                'slug' => 'frisco-cat-cave-gato',
                'description' => 'Cama cueva acogedora para gatos. Diseño de cueva proporciona privacidad y seguridad. Tela suave y relleno grueso para máximo confort. Lavable en máquina.',
                'price' => 45.99,
                'compare_at_price' => null,
                'sku' => 'FR-CAVE-GRY',
                'stock' => 34,
                'brand_id' => $frisco->id,
                'category_id' => $camas->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1543852786-1cf6624b9987?w=800&auto=format&fit=crop',
            ],
            // Más productos de alimento
            [
                'name' => 'Purina Pro Plan Adulto - 14kg',
                'slug' => 'purina-pro-plan-adulto-14kg',
                'description' => 'Alimento premium para perros adultos de todas las razas. Proteína de alta calidad de pollo como ingrediente principal. Prebióticos naturales para la salud digestiva.',
                'price' => 72.99,
                'compare_at_price' => 85.00,
                'sku' => 'PP-ADULT-14',
                'stock' => 67,
                'brand_id' => $purina->id,
                'category_id' => $alimentoPerros->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1626488955636-5e96b5b23621?w=800&auto=format&fit=crop',
            ],
            [
                'name' => 'Whiskas Carne Pollo - 3kg',
                'slug' => 'whiskas-carne-pollo-3kg',
                'description' => 'Alimento completo para gatos con deliciosos trocitos en salsa real de pollo. Formula balanceada para gatos adultos. Sin colorantes artificiales.',
                'price' => 15.99,
                'compare_at_price' => null,
                'sku' => 'WH-CHK-3',
                'stock' => 89,
                'brand_id' => $whiskas->id,
                'category_id' => $alimentoGatos->id,
                'is_active' => true,
                'image_url' => 'https://images.unsplash.com/photo-1611003228941-98852ba62227?w=800&auto=format&fit=crop',
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        $this->command->info('Productos de mascotas creados exitosamente.');
    }
}
