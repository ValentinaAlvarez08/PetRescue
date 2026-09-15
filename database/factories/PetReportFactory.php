<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\PetReport>
 */
class PetReportFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['perdida', 'encontrada']);

        $pets = ['Max', 'Luna', 'Rocky', 'Bella', 'Toby', 'Nina', 'Simba', 'Coco', null];

        $descriptions = [
            'perdida' => [
                'Se perdió cerca del parque, es muy cariñoso y responde a su nombre.',
                'Se escapó por el portón, tiene collar rojo y está algo asustado.',
                'Última vez visto cerca del centro comercial, es muy tímido con extraños.',
            ],
            'encontrada' => [
                'Lo encontré deambulando solo, parece estar bien cuidado.',
                'Andaba cerca de la avenida principal, tiene collar pero sin placa.',
                'Estaba refugiado bajo un carro durante la lluvia, muy dócil.',
            ],
        ];

        // Coordenadas de ejemplo alrededor de Pasto, Nariño (Colombia).
        $lat = 1.2136 + fake()->randomFloat(5, -0.05, 0.05);
        $lng = -77.2811 + fake()->randomFloat(5, -0.05, 0.05);

        return [
            'management_token' => (string) Str::uuid(),
            'type' => $type,
            'pet_name' => fake()->randomElement($pets),
            'description' => fake()->randomElement($descriptions[$type]),
            'contact_phone' => fake()->numerify('3## ### ####'),
            'latitude' => $lat,
            'longitude' => $lng,
            'location_reference' => fake()->randomElement([
                'Cerca al parque principal',
                'Barrio San Ignacio',
                'Sector la Panamericana',
                'Cerca a la Universidad Mariana',
                null,
            ]),
            'status' => 'activo',
        ];
    }
}
