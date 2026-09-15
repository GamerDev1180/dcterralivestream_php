<?php

use App\Enums\ParticipationType;
use App\Enums\QuestionType;
use App\Enums\RegistrationStatus;
use App\Mail\RegistrationConfirmation;
use App\Models\EmailDomain;
use App\Models\Registration;
use App\Models\RegistrationQuestion;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();

    Setting::setValues([
        'feature_registration_enabled' => true,
        'registration_open' => true,
        'registration_end_time' => now()->addWeek()->format('Y-m-d H:i:s'),
    ]);

    EmailDomain::factory()->create(['domain' => '@student.dcterra.nl']);
});

test('a participant can register through the three steps', function () {
    $selectQuestion = RegistrationQuestion::factory()->select(['Beginner', 'Advanced'])->create(['category' => ParticipationType::OnCamera]);
    $checkboxQuestion = RegistrationQuestion::factory()->create([
        'category' => ParticipationType::OnCamera,
        'question_type' => QuestionType::Checkbox,
        'options' => ['Microphone', 'HD Camera'],
    ]);

    Livewire::test('pages::register')
        ->set('name', 'Sam de Vries')
        ->set('email', '123456@student.dcterra.nl')
        ->call('goToQuestions')
        ->assertHasNoErrors()
        ->assertSet('step', 2)
        ->set("answers.{$selectQuestion->id}", 'Advanced')
        ->set("answers.{$checkboxQuestion->id}", ['Microphone', 'HD Camera'])
        ->call('goToConfirmation')
        ->assertHasNoErrors()
        ->assertSet('step', 3)
        ->assertSee('Sam de Vries')
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSee('Registratie Gelukt!');

    $registration = Registration::sole();

    expect($registration->status)->toBe(RegistrationStatus::Confirmed)
        ->and($registration->participation_type)->toBe(ParticipationType::OnCamera)
        ->and($registration->discord_code)->toMatch('/^DCTerra-[A-Z0-9]{8}$/')
        ->and($registration->answers()->where('registration_question_id', $checkboxQuestion->id)->value('answer'))->toBe('Microphone,HD Camera');

    Mail::assertSent(RegistrationConfirmation::class, fn ($mail) => $mail->hasTo('123456@student.dcterra.nl'));
});

test('the confirmation email contains the discord code and the dutch event date', function () {
    Setting::setValues([
        'event_title' => '24H Livestream 2026',
        'stream_start_time' => '2026-12-15 04:00:00',
    ]);
    $registration = Registration::factory()->create();

    $mail = new RegistrationConfirmation($registration);

    $mail->assertHasSubject('🎉 Welcome to the 24H Livestream 2026!');
    $mail->assertSeeInHtml($registration->discord_code);
    $mail->assertSeeInHtml('dinsdag 15 december 2026 om 04:00');
});

test('only the active on-camera questions are asked', function () {
    $onCameraQuestion = RegistrationQuestion::factory()->create(['category' => ParticipationType::OnCamera]);
    $offCameraQuestion = RegistrationQuestion::factory()->create(['category' => ParticipationType::OffCamera]);
    $inactiveQuestion = RegistrationQuestion::factory()->inactive()->create(['category' => ParticipationType::OnCamera]);

    Livewire::test('pages::register')
        ->set('step', 2)
        ->assertSee($onCameraQuestion->question_text)
        ->assertDontSee($offCameraQuestion->question_text)
        ->assertDontSee($inactiveQuestion->question_text);
});

test('step 1 rejects email addresses from domains that are not allowed', function () {
    Livewire::test('pages::register')
        ->set('name', 'Sam de Vries')
        ->set('email', 'sam@gmail.com')
        ->call('goToQuestions')
        ->assertHasErrors(['email'])
        ->assertSet('step', 1)
        ->assertSee('Gebruik een geldig @student.dcterra.nl e-mailadres');
});

test('nobody can register when no email domains are configured', function () {
    EmailDomain::query()->delete();

    Livewire::test('pages::register')
        ->set('name', 'Sam de Vries')
        ->set('email', '123456@student.dcterra.nl')
        ->call('goToQuestions')
        ->assertHasErrors(['email']);
});

test('step 1 rejects an email address that is already registered', function () {
    Registration::factory()->create(['email' => '123456@student.dcterra.nl']);

    Livewire::test('pages::register')
        ->set('name', 'Sam de Vries')
        ->set('email', '123456@student.dcterra.nl')
        ->call('goToQuestions')
        ->assertHasErrors(['email' => 'unique'])
        ->assertSee('Dit e-mailadres is al geregistreerd voor het evenement');
});

test('step 2 requires the required questions to be answered', function () {
    $question = RegistrationQuestion::factory()->create(['category' => ParticipationType::OnCamera]);

    Livewire::test('pages::register')
        ->set('step', 2)
        ->call('goToConfirmation')
        ->assertHasErrors(["answers.{$question->id}" => 'required'])
        ->assertSet('step', 2)
        ->assertSee("Beantwoord: {$question->question_text}");
});

test('the registration closed page is shown after the end time', function () {
    Setting::setValue('registration_end_time', now()->subMinute()->format('Y-m-d H:i:s'));

    Livewire::test('pages::register')
        ->assertSee('Registratieperiode is Afgelopen')
        ->set('name', 'Sam de Vries')
        ->set('email', '123456@student.dcterra.nl')
        ->call('submit')
        ->assertHasErrors(['registration']);

    expect(Registration::count())->toBe(0);
});

test('a participant can register through the api', function () {
    $question = RegistrationQuestion::factory()->create([
        'category' => ParticipationType::OnCamera,
        'question_type' => QuestionType::Checkbox,
        'options' => ['Microphone', 'HD Camera'],
    ]);

    $response = $this->postJson(route('api.register'), [
        'name' => 'Sam de Vries',
        'email' => '123456@student.dcterra.nl',
        'answers' => [(string) $question->id => 'Microphone,HD Camera'],
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Registration successful')
        ->assertJsonPath('discordCode', Registration::sole()->discord_code);

    expect(Registration::sole()->answers()->sole()->answer)->toBe('Microphone,HD Camera');
});

test('the register api returns the same errors as the original api', function () {
    Registration::factory()->create(['email' => '111111@student.dcterra.nl']);

    $this->postJson(route('api.register'), ['name' => 'Sam', 'email' => 'sam@gmail.com'])
        ->assertStatus(400)
        ->assertJsonPath('error', 'Invalid email domain');

    $this->postJson(route('api.register'), ['name' => 'Sam', 'email' => '111111@student.dcterra.nl'])
        ->assertStatus(409)
        ->assertJsonPath('error', 'Email already registered');
});

test('the register api is closed after the end time', function () {
    Setting::setValue('registration_open', false);

    $this->postJson(route('api.register'), ['name' => 'Sam', 'email' => '123456@student.dcterra.nl'])
        ->assertForbidden();

    expect(Registration::count())->toBe(0);
});

test('check email tells if an email address is already registered', function () {
    Registration::factory()->create(['email' => '111111@student.dcterra.nl']);

    $this->postJson(route('api.check-email'), ['email' => '111111@student.dcterra.nl'])
        ->assertOk()
        ->assertJsonPath('exists', true);

    $this->postJson(route('api.check-email'), ['email' => '222222@student.dcterra.nl'])
        ->assertJsonPath('exists', false);

    $this->postJson(route('api.check-email'), ['email' => 'sam@gmail.com'])
        ->assertStatus(400);
});
