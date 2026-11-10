<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Sprint 3 — HU-17.
 *
 * @extends Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'management_token' => (string) Str::uuid(),
            'topic' => fake()->randomElement(array_keys(config('community.topics'))),
            'nickname' => fake()->randomElement(['Laura y Toby', 'Andrés', 'Mamá de Luna', 'Camilo', 'Sofía G.']),
            'title' => fake()->randomElement([
                '¿Cómo lograron que su perro dejara de ladrar a otros perros?',
                'Primera semana con un gato rescatado',
                '¿Cada cuánto desparasitan a sus mascotas?',
            ]),
            'body' => 'Quería compartir mi experiencia y saber qué les ha funcionado a ustedes. Cualquier consejo me sirve mucho.',
            'photo_path' => null,
            'helpful_count' => 0,
        ];
    }

    public function inTopic(string $topic): static
    {
        return $this->state(fn () => ['topic' => $topic]);
    }
}
