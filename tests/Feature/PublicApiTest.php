<?php

use App\Enums\ParticipationType;
use App\Models\FaqItem;
use App\Models\Registration;
use App\Models\RegistrationQuestion;
use App\Models\ScheduleEvent;
use App\Models\Setting;
use App\Models\Sponsor;

test('event config returns saved settings merged with the defaults', function () {
    Setting::setValue('event_title', 'Mega Stream 2027');

    $this->getJson(route('api.event-config'))
        ->assertOk()
        ->assertJsonPath('event_title', 'Mega Stream 2027')
        ->assertJsonPath('org_name', Setting::DEFAULTS['org_name']);
});

test('only active faq items are returned', function () {
    FaqItem::factory()->create(['question' => 'Zichtbaar?']);
    FaqItem::factory()->inactive()->create();

    $this->getJson(route('api.faq'))
        ->assertOk()
        ->assertJsonCount(1, 'items')
        ->assertJsonPath('items.0.question', 'Zichtbaar?');
});

test('only active sponsors are returned', function () {
    Sponsor::factory()->create(['name' => 'Zichtbaar BV', 'website' => 'https://zichtbaar.nl']);
    Sponsor::factory()->inactive()->create();

    $this->getJson(route('api.sponsors'))
        ->assertOk()
        ->assertJsonCount(1, 'sponsors')
        ->assertJsonPath('sponsors.0.name', 'Zichtbaar BV')
        ->assertJsonPath('sponsors.0.website', 'https://zichtbaar.nl');
});

test('only active schedule events are returned', function () {
    ScheduleEvent::factory()->create(['title' => 'Mario Kart', 'event_type' => 'gaming']);
    ScheduleEvent::factory()->inactive()->create();

    $this->getJson(route('api.schedule-events'))
        ->assertOk()
        ->assertJsonCount(1, 'events')
        ->assertJsonPath('events.0.event_type', 'gaming');
});

test('only active on-camera questions are returned', function () {
    $question = RegistrationQuestion::factory()->create(['category' => ParticipationType::OnCamera]);
    RegistrationQuestion::factory()->create(['category' => ParticipationType::OffCamera]);
    RegistrationQuestion::factory()->inactive()->create(['category' => ParticipationType::OnCamera]);

    $this->getJson(route('api.questions'))
        ->assertOk()
        ->assertJsonCount(1, 'questions')
        ->assertJsonPath('questions.0.id', $question->id)
        ->assertJsonPath('questions.0.required', true);
});

test('registration status tells if registration is open', function () {
    Setting::setValues([
        'feature_registration_enabled' => true,
        'registration_open' => false,
    ]);

    $this->getJson(route('api.registration-status'))
        ->assertOk()
        ->assertJsonPath('isOpen', false)
        ->assertJsonPath('manuallyDisabled', true)
        ->assertJsonPath('featureEnabled', true);
});

test('live stats count confirmed participants and show the funds raised', function () {
    Registration::factory()->count(2)->confirmed()->create();
    Registration::factory()->create();
    Setting::setValue('stat_funds_raised_amount', '€1.234');

    $this->getJson(route('api.live-stats'))
        ->assertOk()
        ->assertJsonPath('confirmedParticipants', 2)
        ->assertJsonPath('fundsRaisedAmount', '€1.234');
});
