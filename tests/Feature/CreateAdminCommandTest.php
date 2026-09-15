<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('an admin can be created from the terminal', function () {
    $this->artisan('app:create-admin')
        ->expectsQuestion('Admin username', 'organisator')
        ->expectsQuestion('Admin email', 'organisator@dcterra.nl')
        ->expectsQuestion('Admin password (min 12 chars)', 'een-lang-wachtwoord')
        ->expectsQuestion('Role', 'super_admin')
        ->assertSuccessful();

    $admin = User::firstWhere('username', 'organisator');

    expect($admin->role)->toBe(UserRole::SuperAdmin)
        ->and(Hash::check('een-lang-wachtwoord', $admin->password))->toBeTrue();
});

test('running the command again updates the existing admin', function () {
    User::factory()->create(['username' => 'organisator']);

    $this->artisan('app:create-admin')
        ->expectsQuestion('Admin username', 'organisator')
        ->expectsQuestion('Admin email', 'nieuw@dcterra.nl')
        ->expectsQuestion('Admin password (min 12 chars)', 'een-lang-wachtwoord')
        ->expectsQuestion('Role', 'admin')
        ->assertSuccessful();

    expect(User::count())->toBe(1)
        ->and(User::sole()->email)->toBe('nieuw@dcterra.nl');
});

test('a short password is refused', function () {
    $this->artisan('app:create-admin')
        ->expectsQuestion('Admin username', 'organisator')
        ->expectsQuestion('Admin email', 'organisator@dcterra.nl')
        ->expectsQuestion('Admin password (min 12 chars)', 'kort')
        ->expectsQuestion('Role', 'admin')
        ->assertFailed();

    expect(User::count())->toBe(0);
});
