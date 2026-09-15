<?php

namespace Database\Factories;

use App\Models\ArchivedRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArchivedRecord>
 */
class ArchivedRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'record_type' => 'registration',
            'data' => ['name' => fake()->name(), 'email' => fake()->safeEmail()],
            'archived_year' => now()->year,
            'archived_at' => now(),
        ];
    }
}
