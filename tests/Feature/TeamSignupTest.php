<?php

use App\Enums\TeamSignupStatus;
use App\Enums\TeamSignupType;
use App\Models\Setting;
use App\Models\TeamSignup;
use Livewire\Livewire;

test('a volunteer can sign up for the team', function () {
    Livewire::test('pages::team')
        ->set('type', 'vrijwilliger')
        ->set('name', 'Sam de Vries')
        ->set('email', 'sam@student.dcterra.nl')
        ->set('discord_name', 'sam_dv')
        ->call('signUp')
        ->assertHasNoErrors()
        ->assertSee('Bedankt voor je aanmelding!');

    $teamSignup = TeamSignup::sole();

    expect($teamSignup->type)->toBe(TeamSignupType::Volunteer)
        ->and($teamSignup->status)->toBe(TeamSignupStatus::Pending)
        ->and($teamSignup->discord_name)->toBe('sam_dv');
});

test('a discord name is required', function () {
    Livewire::test('pages::team')
        ->set('type', 'organisatie')
        ->set('name', 'Kim Jansen')
        ->set('email', 'kim@bedrijf.nl')
        ->call('signUp')
        ->assertHasErrors(['discord_name' => 'required']);

    expect(TeamSignup::count())->toBe(0);
});

test('the team page shows a message when the feature is disabled', function () {
    Setting::setValue('feature_team_enabled', false);

    $this->get(route('team'))
        ->assertOk()
        ->assertSee('Aanmelden is nog niet beschikbaar');
});

test('a team signup can be created through the api with the original field names', function () {
    $this->postJson(route('api.team-signup'), [
        'type' => 'organisatie',
        'name' => 'Kim Jansen',
        'organizationName' => 'Bedrijf BV',
        'email' => 'kim@bedrijf.nl',
        'discordNaam' => 'kim_j',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Aanmelding succesvol ontvangen');

    expect(TeamSignup::sole())
        ->organization_name->toBe('Bedrijf BV')
        ->discord_name->toBe('kim_j');
});
