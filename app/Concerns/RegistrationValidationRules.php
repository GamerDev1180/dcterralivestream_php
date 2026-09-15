<?php

namespace App\Concerns;

use App\Enums\ParticipationType;
use App\Enums\QuestionType;
use App\Models\Registration;
use App\Models\RegistrationQuestion;
use App\Rules\AllowedEmailDomain;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

/**
 * Validation rules for the livestream registration.
 *
 * Used by both the Livewire registration wizard and the API, so the rules only live in one place.
 */
trait RegistrationValidationRules
{
    /**
     * Get the validation rules for step 1: name and email.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function personalInfoRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', new AllowedEmailDomain, Rule::unique(Registration::class)],
        ];
    }

    /**
     * Get the validation rules for step 2: the answers to the registration questions.
     *
     * @param  Collection<int, RegistrationQuestion>  $questions
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function answerRules(Collection $questions): array
    {
        $rules = ['answers' => ['array']];

        foreach ($questions as $question) {
            $rules["answers.{$question->id}"] = [
                $question->is_required ? 'required' : 'nullable',
                ...match ($question->question_type) {
                    QuestionType::Text => ['string', 'max:255'],
                    QuestionType::Textarea => ['string', 'max:5000'],
                    QuestionType::Select, QuestionType::Radio => [Rule::in($question->options ?? [])],
                    QuestionType::Checkbox => ['array'],
                },
            ];

            if ($question->question_type === QuestionType::Checkbox) {
                $rules["answers.{$question->id}.*"] = [Rule::in($question->options ?? [])];
            }
        }

        return $rules;
    }

    /**
     * Get readable names for the question fields, so errors show the question instead of "answers.3".
     *
     * @param  Collection<int, RegistrationQuestion>  $questions
     * @return array<string, string>
     */
    protected function answerAttributes(Collection $questions): array
    {
        return $questions
            ->mapWithKeys(fn (RegistrationQuestion $question) => ["answers.{$question->id}" => $question->question_text])
            ->all();
    }

    /**
     * Get the custom (Dutch) validation messages of the registration.
     *
     * @return array<string, string>
     */
    protected function registrationMessages(): array
    {
        return [
            'name.required' => 'Vul je naam in',
            'email.required' => 'Vul je e-mailadres in',
            'email.email' => 'Vul een geldig e-mailadres in',
            'email.unique' => 'Dit e-mailadres is al geregistreerd voor het evenement',
            'answers.*.required' => 'Beantwoord: :attribute',
        ];
    }

    /**
     * Get the active questions shown during registration.
     *
     * Everyone who registers takes part on camera, so only the on-camera questions are asked.
     *
     * @return Collection<int, RegistrationQuestion>
     */
    protected function registrationQuestions(): Collection
    {
        return RegistrationQuestion::activeInOrder()->where('category', ParticipationType::OnCamera)->get();
    }
}
