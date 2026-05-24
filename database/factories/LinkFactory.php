<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'domain_id' => null,
            'original_url' => $this->faker->url(),
            'hash' => Str::random(7),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'is_active' => true,
            'settings' => [],
            'expires_at' => null,
        ];
    }

    /**
     * Indicate that the link is for a guest.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
