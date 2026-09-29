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

        // Especie + razas/colores/tamaños típicos, para que los datos queden coherentes.
        $profiles = [
            [
                'species' => 'Perro',
                'breeds' => ['Labrador', 'Criollo', 'Schnauzer', 'Pastor alemán', 'Salchicha'],
                'colors' => ['Café', 'Negro', 'Blanco y negro', 'Dorado'],
                'sizes' => ['pequeño', 'mediano', 'grande'],
            ],
            [
                'species' => 'Gato',
                'breeds' => ['Criollo', 'Siamés', 'Persa', null],
                'colors' => ['Gris', 'Naranja', 'Blanco', 'Atigrado'],
                'sizes' => ['pequeño', 'mediano'],
            ],
        ];
        $profile = fake()->randomElement($profiles);

        // Coordenadas de ejemplo alrededor de Pasto, Nariño (Colombia).
        $lat = 1.2136 + fake()->randomFloat(5, -0.05, 0.05);
        $lng = -77.2811 + fake()->randomFloat(5, -0.05, 0.05);

        return [
            'management_token' => (string) Str::uuid(),
            'type' => $type,
            'species' => $profile['species'],
            'breed' => $type === 'perdida'
                ? fake()->randomElement($profile['breeds'])
                : null,
            'color' => fake()->randomElement($profile['colors']),
            'size' => $type === 'encontrada'
                ? fake()->randomElement($profile['sizes'])
                : null,
            'pet_name' => fake()->randomElement($pets),
            'description' => fake()->randomElement($descriptions[$type]),
            'contact_phone' => fake()->numerify('3## ### ####'),
            'contact_email' => fake()->boolean(50) ? fake()->safeEmail() : null,
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

    /**
     * Fuerza el tipo "perdida", recalculando breed/size/description acorde
     * (definition() los calcula según un tipo aleatorio; sobreescribir solo
     * "type" con create() los dejaría inconsistentes).
     */
    public function perdida(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'perdida',
                'breed' => $attributes['breed'] ?? fake()->randomElement(['Labrador', 'Criollo', 'Schnauzer', 'Pastor alemán', 'Salchicha', 'Siamés', 'Persa', null]),
                'size' => null,
                'description' => fake()->randomElement([
                    'Se perdió cerca del parque, es muy cariñoso y responde a su nombre.',
                    'Se escapó por el portón, tiene collar rojo y está algo asustado.',
                    'Última vez visto cerca del centro comercial, es muy tímido con extraños.',
                ]),
            ];
        });
    }

    /**
     * Fuerza el tipo "encontrada" (ver nota en perdida()).
     */
    public function encontrada(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'encontrada',
                'breed' => null,
                'size' => fake()->randomElement(['pequeño', 'mediano', 'grande']),
                'description' => fake()->randomElement([
                    'Lo encontré deambulando solo, parece estar bien cuidado.',
                    'Andaba cerca de la avenida principal, tiene collar pero sin placa.',
                    'Estaba refugiado bajo un carro durante la lluvia, muy dócil.',
                ]),
            ];
        });
    }
}
