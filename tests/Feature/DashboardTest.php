<?php

use App\Models\User;

dataset('admin pages', [
    'admin.overview',
    'admin.registrations',
    'admin.team-signups',
    'admin.schedule',
    'admin.event-timing',
    'admin.site-settings',
    'admin.sponsors',
    'admin.faq',
    'admin.email-domains',
    'admin.questions',
    'admin.settings',
]);

test('guests are redirected to the login page', function (string $routeName) {
    $response = $this->get(route($routeName));
    $response->assertRedirect(route('login'));
})->with('admin pages');

test('admins can visit the admin pages', function (string $routeName) {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route($routeName));
    $response->assertOk();
})->with('admin pages');

test('the admin navigation only shows the pages of the group you are in', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.sponsors'))
        ->assertOk()
        ->assertSeeInOrder(['Aanmeldingen', 'Evenement', 'Website', 'Beheer'])
        ->assertSeeText('Site-instellingen')
        ->assertSeeText('Sponsoren')
        ->assertDontSeeText('Registraties')
        ->assertDontSeeText('Programma');
});

test('a group with a single page does not render a second navigation row', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.settings'))
        ->assertOk()
        ->assertSeeText('Beheer')
        ->assertDontSee('aria-label="Beheer"', escape: false);
});
