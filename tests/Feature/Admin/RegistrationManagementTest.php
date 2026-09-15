<?php

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('registrations can be searched and filtered', function () {
    $confirmed = Registration::factory()->confirmed()->onCamera()->create(['name' => 'Sam de Vries']);
    $pending = Registration::factory()->offCamera()->create(['name' => 'Kim Jansen']);

    Livewire::test('pages::admin.registrations')
        ->set('statusFilter', 'confirmed')
        ->assertSee($confirmed->email)
        ->assertDontSee($pending->email)
        ->set('statusFilter', 'all')
        ->set('cameraFilter', 'off_camera')
        ->assertSee($pending->email)
        ->assertDontSee($confirmed->email)
        ->set('cameraFilter', 'all')
        ->set('search', 'sam de')
        ->assertSee($confirmed->email)
        ->assertDontSee($pending->email);
});

test('an admin can confirm and delete a registration', function () {
    $registration = Registration::factory()->create();
    RegistrationAnswer::factory()->for($registration)->create();

    Livewire::test('pages::admin.registrations')
        ->call('updateStatus', $registration->id, 'confirmed')
        ->assertDispatched('registrations-changed');

    expect($registration->fresh()->status)->toBe(RegistrationStatus::Confirmed);

    Livewire::test('pages::admin.registrations')->call('delete', $registration->id);

    expect(Registration::count())->toBe(0)
        ->and(RegistrationAnswer::count())->toBe(0);
});

test('the stats overview counts the registrations', function () {
    Registration::factory()->count(2)->confirmed()->onCamera()->create();
    Registration::factory()->offCamera()->create();

    Livewire::test('admin.stats-overview')
        ->assertSeeInOrder(['Total Registrations', '3', 'On Camera', '2', 'Behind Scenes', '1', 'Confirmed', '2', 'Pending', '1']);
});

test('registrations can be exported as csv', function () {
    $registration = Registration::factory()->onCamera()->create();

    $response = $this->get(route('admin.registrations.export'));

    $response->assertOk()->assertDownload();

    expect($response->streamedContent())
        ->toContain('ID,Name,Email,Type,"Discord Code",Status,"Registration Date"')
        ->toContain($registration->email)
        ->toContain('On Camera');
});

test('guests can not export registrations', function () {
    auth()->logout();

    $this->get(route('admin.registrations.export'))->assertRedirect(route('login'));
});
