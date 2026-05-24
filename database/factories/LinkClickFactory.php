<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\LinkClick;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LinkClick>
 */
class LinkClickFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link_id' => Link::factory(),
            'ip_address' => $this->maskIp($this->faker->ipv4()),
            'user_agent' => $this->faker->userAgent(),
            'referer' => $this->faker->url(),
            'country' => $this->faker->countryCode(),
            'city' => $this->faker->city(),
            'is_robot' => false,
        ];
    }

    private function maskIp(string $ip): string
    {
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            $parts[3] = '0';

            return implode('.', $parts);
        }

        return $ip; // Simplistic masking
    }
}
