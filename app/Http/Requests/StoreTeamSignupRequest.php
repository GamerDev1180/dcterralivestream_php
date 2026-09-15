<?php

namespace App\Http\Requests;

use App\Concerns\TeamSignupValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeamSignupRequest extends FormRequest
{
    use TeamSignupValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The original API uses camelCase field names ("organizationName", "discordNaam"); map them to the database columns.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'organization_name' => $this->input('organizationName', $this->input('organization_name')),
            'discord_name' => $this->input('discordNaam', $this->input('discord_name')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->teamSignupRules();
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->teamSignupMessages();
    }
}
