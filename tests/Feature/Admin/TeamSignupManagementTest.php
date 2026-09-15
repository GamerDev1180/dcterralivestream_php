<?php

use App\Enums\TeamSignupStatus;
use App\Models\TeamSignup;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('team signups can be filtered by type and searched by discord name', function () {
    $volunteer = TeamSignup::factory()->create(['discord_name' => 'gamer_sam']);
    $organisation = TeamSignup::factory()->organisation()->create();

    Livewire::test('pages::admin.team-signups')
        ->set('typeFilter', 'organisatie')
        ->assertSee($organisation->email)
        ->assertDontSee($volunteer->email)
        ->set('typeFilter', 'all')
        ->set('search', 'gamer_sam')
        ->assertSee($volunteer->email)
        ->assertDontSee($organisation->email);
});

test('an admin can approve, reject and delete a team signup', function () {
    $teamSignup = TeamSignup::factory()->create();

    $component = Livewire::test('pages::admin.team-signups');

    $component->call('updateStatus', $teamSignup->id, 'approved');
    expect($teamSignup->fresh()->status)->toBe(TeamSignupStatus::Approved);

    $component->call('updateStatus', $teamSignup->id, 'rejected');
    expect($teamSignup->fresh()->status)->toBe(TeamSignupStatus::Rejected);

    $component->call('delete', $teamSignup->id);
    expect(TeamSignup::count())->toBe(0);
});
