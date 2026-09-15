<?php

namespace App\Rules;

use App\Models\EmailDomain;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Only allows email addresses that end with one of the domains configured in the admin panel.
 */
class AllowedEmailDomain implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || EmailDomain::allows($value)) {
            return;
        }

        $domains = EmailDomain::orderBy('domain')->pluck('domain');

        $fail($domains->isEmpty()
            ? 'Registreren is op dit moment niet mogelijk.'
            : 'Gebruik een geldig '.$domains->join(', ', ' of ').' e-mailadres');
    }
}
