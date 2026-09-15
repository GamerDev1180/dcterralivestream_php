<?php

namespace Database\Factories;

use App\Enums\ParticipationType;
use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->numerify('######').'@student.dcterra.nl',
            'participation_type' => fake()->randomElement(ParticipationType::cases()),
            'status' => RegistrationStatus::Pending,
            'discord_code' => 'DCTerra-'.Str::upper(Str::random(8)),
        ];
    }

    /**
     * Indicate that the participant wants to be on camera.
     */
    public function onCamera(): static
    {
        return $this->state(fn (array $attributes) => [
            'participation_type' => ParticipationType::OnCamera,
        ]);
    }

    /**
     * Indicate that the participant works behind the scenes.
     */
    public function offCamera(): static
    {
        return $this->state(fn (array $attributes) => [
            'participation_type' => ParticipationType::OffCamera,
        ]);
    }

    /**
     * Indicate that the registration has been confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegistrationStatus::Confirmed,
        ]);
    }
}
