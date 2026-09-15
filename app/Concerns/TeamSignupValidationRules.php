<?php

namespace App\Concerns;

use App\Enums\TeamSignupType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Validation rules for the volunteer / organisation signup form.
 */
trait TeamSignupValidationRules
{
    /**
     * Get the validation rules for a team signup.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function teamSignupRules(): array
    {
        return [
            'type' => ['required', Rule::enum(TeamSignupType::class)],
            'name' => ['required', 'string', 'max:255'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
            'discord_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get the custom (Dutch) validation messages of a team signup.
     *
     * @return array<string, string>
     */
    protected function teamSignupMessages(): array
    {
        return [
            'name.required' => 'Naam is verplicht',
            'email.required' => 'E-mailadres is verplicht',
            'email.email' => 'Ongeldig e-mailadres',
            'discord_name.required' => 'Discord naam is verplicht',
        ];
    }
}
