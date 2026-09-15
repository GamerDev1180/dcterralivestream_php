<?php

namespace Database\Factories;

use App\Enums\ScheduleEventType;
use App\Models\ScheduleEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleEvent>
 */
class ScheduleEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('+1 week', '+2 weeks');

        return [
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'start_time' => $startTime,
            'end_time' => (clone $startTime)->modify('+1 hour'),
            'event_type' => fake()->randomElement(ScheduleEventType::cases()),
            'participants' => [fake()->firstName(), fake()->firstName()],
            'display_order' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the event is hidden from the public schedule.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
