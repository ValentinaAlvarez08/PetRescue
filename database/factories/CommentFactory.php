<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Sprint 3 — HU-18.
 *
 * @extends Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'nickname' => fake()->randomElement(['Diana', 'Julián', 'Papá de Rocky', 'Valeria']),
            'body' => fake()->randomElement([
                'A nosotros nos funcionó tener mucha paciencia y premiar cada avance.',
                'Te recomiendo consultarlo con el veterinario antes de cambiar algo.',
                'Me pasó lo mismo, con el tiempo mejoró bastante. ¡Ánimo!',
            ]),
        ];
    }
}
