<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    /**
     * Ejecuta los seeders de la base de datos.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::firstOrCreate(
            ['email' => 'admin@ecommerce.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password123'),
                'is_admin' => true,
            ]
        );

        // Crear clientes normales
        User::firstOrCreate(
            ['email' => 'cliente1@ecommerce.com'],
            [
                'name' => 'Cliente Uno',
                'password' => Hash::make('password123'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'cliente2@ecommerce.com'],
            [
                'name' => 'Cliente Dos',
                'password' => Hash::make('password123'),
            ]
        );

        $this->command->info('Usuarios creados exitosamente:');
        $this->command->info('  - Admin: admin@ecommerce.com / password123');
        $this->command->info('  - Cliente 1: cliente1@ecommerce.com / password123');
        $this->command->info('  - Cliente 2: cliente2@ecommerce.com / password123');
    }
}
