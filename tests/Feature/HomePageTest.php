<?php

use App\Models\FaqItem;
use App\Models\Setting;

test('home page shows the event title from the settings', function () {
    Setting::setValue('event_title', 'Mega Stream 2027');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Mega Stream 2027');
});

test('home page falls back to default stats when they are not filled in', function () {
    Setting::setValues([
        'stat_expected_participants' => '0',
        'stat_funds_goal_amount' => '€10.000',
    ]);

    $this->get(route('home'))
        ->assertSee('100+')
        ->assertSee('€10.000');
});

test('home page only shows the faq when the feature is enabled', function () {
    $faqItem = FaqItem::factory()->create(['question' => 'Mag ik mijn eigen laptop meenemen?']);

    Setting::setValue('feature_faq_enabled', false);
    $this->get(route('home'))->assertDontSee($faqItem->question);

    Setting::setValue('feature_faq_enabled', true);
    $this->get(route('home'))->assertSee($faqItem->question);
});

test('home page shows the timers and closed registration after the end time', function () {
    Setting::setValues([
        'feature_registration_enabled' => true,
        'stream_start_time' => now()->addWeek()->format('Y-m-d H:i:s'),
        'registration_end_time' => now()->subDay()->format('Y-m-d H:i:s'),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Stream Begint Over')
        ->assertSee('De registratieperiode is beëindigd');
});
