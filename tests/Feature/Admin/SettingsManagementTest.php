<?php

use App\Models\ArchivedRecord;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\Setting;
use App\Models\Sponsor;
use App\Models\TeamSignup;
use App\Models\User;
use Livewire\Livewire;

test('a feature flag is saved as soon as it is switched', function () {
    $this->actingAs(User::factory()->create());
    Setting::setValue('feature_schedule_enabled', false);

    Livewire::test('pages::admin.site-settings')
        ->set('features.feature_schedule_enabled', true);

    expect(Setting::isFeatureEnabled('schedule'))->toBeTrue();
});

test('site settings are saved when a field changes and invalid values are rejected', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::admin.site-settings')
        ->set('values.org_name', 'Stichting Goed Doel')
        ->set('values.stat_funds_raised_amount', '€1.234')
        ->assertHasNoErrors()
        ->set('values.contact_email', 'geen-email')
        ->assertHasErrors(['values.contact_email']);

    expect(Setting::getValue('org_name'))->toBe('Stichting Goed Doel')
        ->and(Setting::getValue('stat_funds_raised_amount'))->toBe('€1.234')
        ->and(Setting::getValue('contact_email'))->toBe(Setting::DEFAULTS['contact_email']);
});

test('event timing is saved when a field changes', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::admin.event-timing')
        ->set('stream_start_time', '2026-12-15T04:00')
        ->set('event_title', '24H Livestream 2026')
        ->set('registration_open', false)
        ->assertHasNoErrors();

    expect(Setting::getValue('stream_start_time'))->toBe('2026-12-15 04:00:00')
        ->and(Setting::getValue('event_title'))->toBe('24H Livestream 2026')
        ->and(Setting::getBoolean('registration_open'))->toBeFalse();
});

test('a normal admin can not reset the event', function () {
    $this->actingAs(User::factory()->create());
    Registration::factory()->create();

    Livewire::test('pages::admin.settings')
        ->call('archiveAndReset')
        ->assertSet('resetMessage', 'Insufficient permissions');

    expect(Registration::count())->toBe(1);
});

test('archive and reset moves registrations and signups to the archive and keeps the rest', function () {
    $this->actingAs(User::factory()->superAdmin()->create());

    RegistrationAnswer::factory()->count(2)->create();
    TeamSignup::factory()->create();
    Sponsor::factory()->create();

    Livewire::test('pages::admin.settings')
        ->call('archiveAndReset')
        ->assertSee('archived under year '.now()->year);

    expect(Registration::count())->toBe(0)
        ->and(RegistrationAnswer::count())->toBe(0)
        ->and(TeamSignup::count())->toBe(0)
        ->and(Sponsor::count())->toBe(1)
        ->and(ArchivedRecord::where('record_type', 'registration')->count())->toBe(2)
        ->and(ArchivedRecord::where('record_type', 'team_signup')->count())->toBe(1)
        ->and(ArchivedRecord::first()->archived_year)->toBe(now()->year);
});
