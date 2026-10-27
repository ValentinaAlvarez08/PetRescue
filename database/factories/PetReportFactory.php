<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\PetReport>
 */
class PetReportFactory extends Factory
{
    private const DESCRIPTIONS = [
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

    /**
     * Especie + razas/colores típicos, para que los datos queden coherentes.
     * Las llaves y razas salen de config/pets.php (Sprint 2 — HU-13).
     */
    private const PROFILES = [
        ['species' => 'perro', 'breeds' => ['Labrador', 'Criollo / mestizo', 'Schnauzer', 'Pastor alemán', 'Beagle'], 'colors' => ['cafe', 'negro', 'blanco', 'crema']],
        ['species' => 'gato', 'breeds' => ['Criollo / mestizo', 'Siamés', 'Persa'], 'colors' => ['gris', 'naranja', 'blanco', 'atigrado']],
    ];

    public function definition(): array
    {
        $type = fake()->randomElement(['perdida', 'encontrada']);
        $profile = fake()->randomElement(self::PROFILES);

        $pets = ['Max', 'Luna', 'Rocky', 'Bella', 'Toby', 'Nina', 'Simba', 'Coco', null];

        // Coordenadas de ejemplo alrededor de Pasto, Nariño (Colombia).
        $lat = 1.2136 + fake()->randomFloat(5, -0.05, 0.05);
        $lng = -77.2811 + fake()->randomFloat(5, -0.05, 0.05);

        return [
            'management_token' => (string) Str::uuid(),
            'type' => $type,
            'species' => $profile['species'],
            'breed' => $type === 'perdida' ? fake()->randomElement($profile['breeds']) : null,
            'color' => implode(',', fake()->randomElements($profile['colors'], fake()->numberBetween(1, 2))),
            'size' => $profile['species'] === 'perro' ? fake()->randomElement(['pequeño', 'mediano', 'grande']) : null,
            'sex' => fake()->randomElement(['macho', 'hembra', 'no_se']),
            'event_date' => now()->subDays(fake()->numberBetween(0, 20))->toDateString(),
            'pet_name' => fake()->randomElement($pets),
            'description' => fake()->randomElement(self::DESCRIPTIONS[$type]),
            'contact_phone' => fake()->numerify('3#########'),
            'contact_email' => fake()->boolean(50) ? fake()->safeEmail() : null,
            'contact_whatsapp' => true,
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
     * Fuerza el tipo "perdida", recalculando raza y descripción acorde
     * (definition() los calcula según un tipo aleatorio; sobreescribir solo
     * "type" con create() los dejaría inconsistentes).
     */
    public function perdida(): static
    {
        return $this->state(function (array $attributes) {
            $breeds = collect(self::PROFILES)->firstWhere('species', $attributes['species'])['breeds'] ?? ['Criollo / mestizo'];

            return [
                'type' => 'perdida',
                'breed' => $attributes['breed'] ?? fake()->randomElement($breeds),
                'description' => fake()->randomElement(self::DESCRIPTIONS['perdida']),
            ];
        });
    }

    /**
     * Fuerza el tipo "encontrada" (ver nota en perdida()).
     */
    public function encontrada(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'encontrada',
            'breed' => null,
            'description' => fake()->randomElement(self::DESCRIPTIONS['encontrada']),
        ]);
    }
}
