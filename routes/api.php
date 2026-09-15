<?php

use App\Http\Controllers\Api\CheckEmailController;
use App\Http\Controllers\Api\EventConfigController;
use App\Http\Controllers\Api\FaqItemController;
use App\Http\Controllers\Api\LiveStatsController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\RegistrationStatusController;
use App\Http\Controllers\Api\ScheduleEventController;
use App\Http\Controllers\Api\SponsorController;
use App\Http\Controllers\Api\TeamSignupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
|
| These endpoints are prefixed with "/api" and use the same paths and JSON
| as the original Next.js API, so other apps (e.g. a stream overlay) keep working.
| Everything that needs a login is handled by the Livewire admin panel instead.
|
*/

Route::get('event-config', [EventConfigController::class, 'show'])->name('api.event-config');
Route::get('registration-status', RegistrationStatusController::class)->name('api.registration-status');
Route::get('public/live-stats', LiveStatsController::class)->name('api.live-stats');
Route::get('faq', [FaqItemController::class, 'index'])->name('api.faq');
Route::get('sponsors', [SponsorController::class, 'index'])->name('api.sponsors');
Route::get('schedule-events', [ScheduleEventController::class, 'index'])->name('api.schedule-events');
Route::get('questions', [QuestionController::class, 'index'])->name('api.questions');

Route::post('check-email', CheckEmailController::class)
    ->middleware('throttle:20,10')
    ->name('api.check-email');

Route::post('register', [RegistrationController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('api.register');

Route::post('team-signup', [TeamSignupController::class, 'store'])
    ->middleware('throttle:10,10')
    ->name('api.team-signup');
