<?php

namespace Database\Factories;

use App\Models\EmailDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailDomain>
 */
class EmailDomainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain' => '@'.fake()->unique()->domainName(),
        ];
    }
}
