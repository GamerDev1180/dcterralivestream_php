<?php

use App\Http\Controllers\Admin\ExportRegistrationsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
|
| Pages of features that are switched off in "Site Settings" show a
| "not available yet" message instead of their content.
|
*/

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('register', 'pages::register')->name('register');
Route::livewire('schedule', 'pages::schedule')->name('schedule');
Route::livewire('sponsors', 'pages::sponsors')->name('sponsors');
Route::livewire('team', 'pages::team')->name('team');

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
|
| Every tab of the admin dashboard is its own Livewire page.
|
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/', 'pages::admin.registrations')->name('registrations');
    Route::get('registrations/export', ExportRegistrationsController::class)->name('registrations.export');
    Route::livewire('team-signups', 'pages::admin.team-signups')->name('team-signups');
    Route::livewire('schedule', 'pages::admin.schedule')->name('schedule');
    Route::livewire('event-timing', 'pages::admin.event-timing')->name('event-timing');
    Route::livewire('site-settings', 'pages::admin.site-settings')->name('site-settings');
    Route::livewire('sponsors', 'pages::admin.sponsors')->name('sponsors');
    Route::livewire('faq', 'pages::admin.faq')->name('faq');
    Route::livewire('email-domains', 'pages::admin.email-domains')->name('email-domains');
    Route::livewire('questions', 'pages::admin.questions')->name('questions');
    Route::livewire('settings', 'pages::admin.settings')->name('settings');
});

require __DIR__.'/settings.php';
