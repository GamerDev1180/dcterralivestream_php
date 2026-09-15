<?php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\RegistrationQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationAnswer>
 */
class RegistrationAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'registration_question_id' => RegistrationQuestion::factory(),
            'answer' => fake()->sentence(),
        ];
    }
}
