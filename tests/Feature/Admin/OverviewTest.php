<?php

use App\Enums\ParticipationType;
use App\Enums\RegistrationStatus;
use App\Enums\ScheduleEventType;
use App\Enums\SponsorTier;
use App\Enums\TeamSignupStatus;
use App\Enums\TeamSignupType;
use App\Models\Registration;
use App\Models\ScheduleEvent;
use App\Models\Sponsor;
use App\Models\TeamSignup;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-09-20 12:00:00'));
    $this->actingAs(User::factory()->create());
});

test('the registration trend counts signups per bucket and carries the running total', function () {
    Registration::factory()->count(3)->create(['created_at' => '2026-06-01 10:00:00']); // before the window
    Registration::factory()->count(2)->create(['created_at' => '2026-09-19 10:00:00']);
    Registration::factory()->create(['created_at' => '2026-09-20 09:00:00']);

    $overview = Livewire::test('pages::admin.overview')->set('days', 30)->instance();

    expect($overview->registrationsPerBucket)->toHaveCount(30)
        ->and(array_slice($overview->registrationsPerBucket, -3))->toBe([0, 2, 1])
        ->and(array_slice($overview->cumulativeRegistrations, -3))->toBe([3, 5, 6])
        ->and($overview->chartLabels[29]['short'])->toBe('20 sep.');
});

test('the longest period is bucketed per week instead of per day', function () {
    Registration::factory()->create(['created_at' => '2026-09-18 10:00:00']);

    $overview = Livewire::test('pages::admin.overview')->set('days', 90)->instance();
    $labels = $overview->chartLabels;

    expect($overview->registrationsPerBucket)->toHaveCount(13)
        ->and(end($labels)['full'])->toContain('t/m');
});

test('a period that is not offered falls back to 30 days', function () {
    $overview = Livewire::test('pages::admin.overview', ['days' => 4321])->instance();

    expect($overview->days)->toBe(30)
        ->and($overview->registrationsPerBucket)->toHaveCount(30);
});

test('team signups are split into volunteers and organisations', function () {
    TeamSignup::factory()->count(2)->create(['type' => TeamSignupType::Volunteer, 'created_at' => '2026-09-20 08:00:00']);
    TeamSignup::factory()->create(['type' => TeamSignupType::Organisation, 'created_at' => '2026-09-20 08:00:00']);

    $series = Livewire::test('pages::admin.overview')->instance()->teamSignupSeries;
    $volunteers = $series[0]['values'];
    $organisations = $series[1]['values'];

    expect($series)->toHaveCount(2)
        ->and($series[0]['name'])->toBe('Vrijwilliger')
        ->and(end($volunteers))->toBe(2)
        ->and($series[1]['name'])->toBe('Organisatie')
        ->and(end($organisations))->toBe(1);
});

test('the breakdowns count every status and participation type, including the empty ones', function () {
    Registration::factory()->count(2)->create(['status' => RegistrationStatus::Confirmed, 'participation_type' => ParticipationType::OnCamera]);
    Registration::factory()->create(['status' => RegistrationStatus::Pending, 'participation_type' => ParticipationType::OffCamera]);
    TeamSignup::factory()->create(['status' => TeamSignupStatus::Approved]);

    $overview = Livewire::test('pages::admin.overview')->instance();

    expect(array_column($overview->registrationStatusSegments, 'value'))->toBe([1, 2, 0])
        ->and(array_column($overview->participationSegments, 'value'))->toBe([2, 1])
        ->and(array_column($overview->teamStatusSegments, 'value'))->toBe([0, 1, 0]);
});

test('only visible sponsors and active programme items are charted', function () {
    Sponsor::factory()->count(2)->create(['tier' => SponsorTier::Gold, 'is_active' => true]);
    Sponsor::factory()->create(['tier' => SponsorTier::Gold, 'is_active' => false]);

    ScheduleEvent::factory()->create([
        'event_type' => ScheduleEventType::Gaming,
        'start_time' => '2026-12-15 10:00:00',
        'end_time' => '2026-12-15 12:30:00',
        'is_active' => true,
    ]);
    ScheduleEvent::factory()->create([
        'event_type' => ScheduleEventType::Gaming,
        'start_time' => '2026-12-15 13:00:00',
        'end_time' => '2026-12-15 14:00:00',
        'is_active' => false,
    ]);

    $overview = Livewire::test('pages::admin.overview')->instance();

    $gold = collect($overview->sponsorRows)->firstWhere('label', 'Goud');
    $gaming = collect($overview->scheduleRows)->firstWhere('label', 'Gaming');

    expect($gold['value'])->toBe(2)
        ->and($gaming['value'])->toBe(2.5)
        ->and($gaming['display'])->toBe('2,5 uur');
});
