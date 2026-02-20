<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Catalog\Models\Brand;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Brand::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $brands = [
            [
                'name' => 'Royal Canin',
                'slug' => 'royal-canin',
                'description' => 'Nutrición precisa y saludable para perros y gatos',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => "Hill's Science Diet",
                'slug' => 'hills-science-diet',
                'description' => 'Alimentación veterinaria desarrollada por científicos',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Purina Pro Plan',
                'slug' => 'purina-pro-plan',
                'description' => 'Fórmulas avanzadas con nutrición de alta calidad',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Blue Buffalo',
                'slug' => 'blue-buffalo',
                'description' => 'Alimento natural para mascotas',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Taste of the Wild',
                'slug' => 'taste-of-the-wild',
                'description' => 'Alimento ancestral con proteínas reales',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'KONG',
                'slug' => 'kong',
                'description' => 'Juguetes duraderos y masticables para mascotas',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Nylabone',
                'slug' => 'nylabone',
                'description' => 'Masticables seguros y saludables para perros',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'PetSafe',
                'slug' => 'petsafe',
                'description' => 'Soluciones de entrenamiento y contención',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Frisco',
                'slug' => 'frisco',
                'description' => 'Camas, accesorios y juguetes para mascotas',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Advantage',
                'slug' => 'advantage',
                'description' => 'Tratamiento contra pulgas y garrapatas',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Frontline',
                'slug' => 'frontline',
                'description' => 'Protección contra parásitos externos',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Whiskas',
                'slug' => 'whiskas',
                'description' => 'Alimento delicioso para gatos',
                'logo' => null,
                'is_active' => true,
            ],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        $this->command->info('Marcas de mascotas creadas exitosamente.');
    }
}
