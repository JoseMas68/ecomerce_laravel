<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Catalog\Models\Category;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            [
                'name' => 'Alimento',
                'slug' => 'alimento',
                'description' => 'Nutrición completa y balanceada para tu mascota',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Juguetes',
                'slug' => 'juguetes',
                'description' => 'Diversión y entretenimiento para perros y gatos',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Camas y Descanso',
                'slug' => 'camas-y-descanso',
                'description' => 'Confort y descanso para tu mejor amigo',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Correas y Collares',
                'slug' => 'correas-y-collares',
                'description' => 'Control y estilo para paseos seguros',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Transportines',
                'slug' => 'transportines',
                'description' => 'Viajes seguros y cómodos',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Higiene y Cuidado',
                'slug' => 'higiene-y-cuidado',
                'description' => 'Mantén a tu mascota limpia y saludable',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Salud y Bienestar',
                'slug' => 'salud-y-bienestar',
                'description' => 'Suplementos y cuidados veterinarios',
                'parent_id' => null,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Crear subcategorías
        $alimentoCategory = Category::where('slug', 'alimento')->first();
        $higieneCategory = Category::where('slug', 'higiene-y-cuidado')->first();

        Category::create([
            'name' => 'Perros',
            'slug' => 'alimento-perros',
            'description' => 'Alimento seco y húmedo para perros',
            'parent_id' => $alimentoCategory->id,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Gatos',
            'slug' => 'alimento-gatos',
            'description' => 'Alimento seco y húmedo para gatos',
            'parent_id' => $alimentoCategory->id,
            'is_active' => true,
        ]);

        $this->command->info('Categorías de mascotas creadas exitosamente.');
    }
}
