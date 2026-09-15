<?php

namespace Database\Factories;

use App\Enums\ParticipationType;
use App\Enums\QuestionType;
use App\Models\RegistrationQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistrationQuestion>
 */
class RegistrationQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_text' => rtrim(fake()->sentence(), '.').'?',
            'question_type' => QuestionType::Text,
            'category' => fake()->randomElement(ParticipationType::cases()),
            'options' => null,
            'order_index' => fake()->numberBetween(0, 10),
            'is_required' => true,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the question is a dropdown with the given options.
     *
     * @param  array<int, string>  $options
     */
    public function select(array $options = ['Optie 1', 'Optie 2', 'Optie 3']): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionType::Select,
            'options' => $options,
        ]);
    }

    /**
     * Indicate that the question is optional.
     */
    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => false,
        ]);
    }

    /**
     * Indicate that the question is hidden from the form.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
