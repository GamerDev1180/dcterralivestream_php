<?php

namespace Database\Factories;

use App\Enums\TeamSignupStatus;
use App\Enums\TeamSignupType;
use App\Models\TeamSignup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamSignup>
 */
class TeamSignupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => TeamSignupType::Volunteer,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'discord_name' => fake()->userName(),
            'organization_name' => null,
            'phone' => null,
            'message' => null,
            'status' => TeamSignupStatus::Pending,
        ];
    }

    /**
     * Indicate that the signup is from an organisation.
     */
    public function organisation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TeamSignupType::Organisation,
            'organization_name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'message' => fake()->paragraph(),
        ]);
    }
}
