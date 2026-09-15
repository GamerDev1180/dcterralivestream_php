<?php

use App\Models\User;

dataset('admin pages', [
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
