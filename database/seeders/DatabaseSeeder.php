<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Datos de prueba para el listado de reportes: mascotas perdidas y
        // encontradas en Pasto, cada una con su foto (public/images/seed/).
        $this->call(PetReportSeeder::class);

        // Sprint 2 — HU-16: directorio de servicios (datos de ejemplo).
        $this->call(BusinessSeeder::class);
    }
}
