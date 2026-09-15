<?php

namespace App\Http\Requests;

use App\Concerns\RegistrationValidationRules;
use App\Enums\QuestionType;
use App\Models\RegistrationQuestion;
use App\Models\Setting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    use RegistrationValidationRules;

    /** @var Collection<int, RegistrationQuestion>|null */
    private ?Collection $questions = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Setting::isRegistrationOpen();
    }

    /**
     * The API receives checkbox answers as "Option A,Option B"; turn them into a list so they can be validated.
     */
    protected function prepareForValidation(): void
    {
        $answers = $this->input('answers');

        if (! is_array($answers)) {
            return;
        }

        foreach ($this->questions()->where('question_type', QuestionType::Checkbox) as $question) {
            if (is_string($answers[$question->id] ?? null)) {
                $answers[$question->id] = array_values(array_filter(explode(',', $answers[$question->id])));
            }
        }

        $this->merge(['answers' => $answers]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Checking the email domain and duplicates happens in the controller, so the API can return the same
     * status codes as the original Next.js API (400 and 409).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            ...$this->answerRules($this->questions()),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->answerAttributes($this->questions());
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->registrationMessages();
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Registration is closed');
    }

    /**
     * Get the registration questions, loading them only once per request.
     *
     * @return Collection<int, RegistrationQuestion>
     */
    private function questions(): Collection
    {
        return $this->questions ??= $this->registrationQuestions();
    }
}
