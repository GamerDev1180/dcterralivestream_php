<?php

use App\Enums\QuestionType;
use App\Models\EmailDomain;
use App\Models\FaqItem;
use App\Models\RegistrationQuestion;
use App\Models\ScheduleEvent;
use App\Models\Sponsor;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('a schedule event can be added with participants and deactivated later', function () {
    Livewire::test('pages::admin.schedule')
        ->set('title', 'Mario Kart toernooi')
        ->set('start_time', '2026-12-15T10:00')
        ->set('end_time', '2026-12-15T11:30')
        ->set('event_type', 'gaming')
        ->set('participants', 'Sam,  Kim , ')
        ->call('save')
        ->assertHasNoErrors();

    $event = ScheduleEvent::sole();

    expect($event->participants)->toBe(['Sam', 'Kim'])
        ->and($event->start_time->format('H:i'))->toBe('10:00');

    Livewire::test('pages::admin.schedule')
        ->call('edit', $event->id)
        ->set('is_active', false)
        ->call('save')
        ->assertHasNoErrors();

    expect($event->fresh()->is_active)->toBeFalse();
});

test('a schedule event must end after it starts', function () {
    Livewire::test('pages::admin.schedule')
        ->set('title', 'Mario Kart toernooi')
        ->set('start_time', '2026-12-15T10:00')
        ->set('end_time', '2026-12-15T09:00')
        ->call('save')
        ->assertHasErrors(['end_time' => 'after']);
});

test('a sponsor can be added and edited and names must be unique', function () {
    Livewire::test('pages::admin.sponsors')
        ->set('name', 'Bedrijf BV')
        ->set('tier', 'Gold')
        ->call('save')
        ->assertHasNoErrors();

    $sponsor = Sponsor::sole();

    Livewire::test('pages::admin.sponsors')
        ->call('edit', $sponsor->id)
        ->assertSet('name', 'Bedrijf BV')
        ->set('is_active', false)
        ->call('save')
        ->assertHasNoErrors();

    expect($sponsor->fresh()->is_active)->toBeFalse();

    Livewire::test('pages::admin.sponsors')
        ->set('name', 'Bedrijf BV')
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('inactive sponsors are still listed in the admin panel', function () {
    $sponsor = Sponsor::factory()->inactive()->create();

    Livewire::test('pages::admin.sponsors')->assertSee($sponsor->name);
});

test('a faq item can be added and deleted', function () {
    Livewire::test('pages::admin.faq')
        ->set('question', 'Is er eten?')
        ->set('answer', 'Ja, pizza!')
        ->call('save')
        ->assertHasNoErrors();

    $faqItem = FaqItem::sole();

    Livewire::test('pages::admin.faq')->call('delete', $faqItem->id);

    expect(FaqItem::count())->toBe(0);
});

test('a dropdown question needs options', function () {
    Livewire::test('pages::admin.questions')
        ->set('question_text', 'How would you like to contribute?')
        ->set('question_type', 'select')
        ->set('category', 'off_camera')
        ->call('save')
        ->assertHasErrors(['options' => 'required'])
        ->set('options', 'Technical Support, Social Media ,Other')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Question added successfully');

    $question = RegistrationQuestion::sole();

    expect($question->question_type)->toBe(QuestionType::Select)
        ->and($question->options)->toBe(['Technical Support', 'Social Media', 'Other']);
});

test('email domains must start with an @ and be unique', function () {
    Livewire::test('pages::admin.email-domains')
        ->set('newDomain', 'student.dcterra.nl')
        ->call('addDomain')
        ->assertHasErrors(['newDomain' => 'starts_with'])
        ->set('newDomain', '@Student.DCTerra.nl')
        ->call('addDomain')
        ->assertHasNoErrors()
        ->assertSee('Domain added successfully')
        ->set('newDomain', '@student.dcterra.nl')
        ->call('addDomain')
        ->assertHasErrors(['newDomain' => 'unique']);

    expect(EmailDomain::sole()->domain)->toBe('@student.dcterra.nl');
});
