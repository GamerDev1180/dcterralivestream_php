<?php

use App\Models\ScheduleEvent;
use App\Models\Setting;
use App\Models\Sponsor;

test('the schedule page shows the active programme when enabled', function () {
    Setting::setValue('feature_schedule_enabled', true);
    $event = ScheduleEvent::factory()->create(['title' => 'Mario Kart toernooi']);
    $inactiveEvent = ScheduleEvent::factory()->inactive()->create(['title' => 'Geheim onderdeel']);

    $this->get(route('schedule'))
        ->assertOk()
        ->assertSee($event->title)
        ->assertDontSee($inactiveEvent->title);
});

test('the sponsors page only shows active sponsors', function () {
    Setting::setValue('feature_sponsors_enabled', true);
    Sponsor::factory()->create(['name' => 'Zichtbaar BV']);
    Sponsor::factory()->inactive()->create(['name' => 'Verborgen BV']);

    $this->get(route('sponsors'))
        ->assertOk()
        ->assertSee('Zichtbaar BV')
        ->assertDontSee('Verborgen BV');
});

test('disabled pages show a message and are hidden from the navigation', function () {
    Setting::setValues([
        'feature_schedule_enabled' => false,
        'feature_sponsors_enabled' => false,
    ]);

    $this->get(route('schedule'))->assertOk()->assertSee('Schema is nog niet beschikbaar');
    $this->get(route('sponsors'))->assertOk()->assertSee('Sponsors pagina is nog niet beschikbaar');

    $this->get(route('home'))
        ->assertDontSee(route('schedule'))
        ->assertDontSee(route('sponsors'));
});
