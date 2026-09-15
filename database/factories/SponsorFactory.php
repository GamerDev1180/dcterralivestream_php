<?php

namespace Database\Factories;

use App\Enums\SponsorTier;
use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sponsor>
 */
class SponsorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'tier' => fake()->randomElement(SponsorTier::cases()),
            'logo_url' => null,
            'description' => fake()->sentence(),
            'website' => fake()->url(),
            'contribution' => fake()->randomElement(['Prijzen', 'Apparatuur', 'Eten en drinken', 'Donatie']),
            'display_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the sponsor is hidden from the public site.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
