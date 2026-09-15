<?php

/*
|--------------------------------------------------------------------------
| Dutch validation messages
|--------------------------------------------------------------------------
|
| Only the rules used in this application are translated. Other rules fall
| back to the English messages (APP_FALLBACK_LOCALE).
|
*/

return [
    'after' => ':Attribute moet een datum na :date zijn.',
    'alpha_dash' => ':Attribute mag alleen letters, cijfers, streepjes en underscores bevatten.',
    'array' => ':Attribute moet een lijst zijn.',
    'boolean' => ':Attribute moet aan of uit zijn.',
    'confirmed' => 'De bevestiging van :attribute komt niet overeen.',
    'date' => ':Attribute is geen geldige datum.',
    'email' => ':Attribute moet een geldig e-mailadres zijn.',
    'enum' => 'De gekozen :attribute is ongeldig.',
    'in' => 'De gekozen :attribute is ongeldig.',
    'integer' => ':Attribute moet een geheel getal zijn.',
    'max' => [
        'string' => ':Attribute mag niet meer dan :max tekens bevatten.',
        'numeric' => ':Attribute mag niet groter zijn dan :max.',
    ],
    'min' => [
        'string' => ':Attribute moet minimaal :min tekens bevatten.',
        'numeric' => ':Attribute moet minimaal :min zijn.',
    ],
    'regex' => 'Het formaat van :attribute is ongeldig.',
    'required' => ':Attribute is verplicht.',
    'required_if' => ':Attribute is verplicht.',
    'string' => ':Attribute moet tekst zijn.',
    'unique' => ':Attribute is al in gebruik.',
    'url' => ':Attribute moet een geldige URL zijn.',

    'attributes' => [
        'name' => 'naam',
        'email' => 'e-mailadres',
        'participation_type' => 'type deelname',
        'question' => 'vraag',
        'answer' => 'antwoord',
        'question_text' => 'vraag',
        'title' => 'titel',
        'description' => 'beschrijving',
        'start_time' => 'starttijd',
        'end_time' => 'eindtijd',
        'website_url' => 'website',
        'org_name' => 'naam organisatie',
        'event_title' => 'titel evenement',
    ],
];
