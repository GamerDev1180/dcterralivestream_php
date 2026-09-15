<?php

use App\Models\FaqItem;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')] #[Title('Home')] class extends Component {
    /**
     * Determine if a public feature is switched on in "Site Settings".
     */
    public function featureEnabled(string $feature): bool
    {
        return Setting::isFeatureEnabled($feature);
    }

    /**
     * The FAQ items, or an empty list when the FAQ is switched off.
     *
     * @return Collection<int, FaqItem>
     */
    #[Computed]
    public function faqItems(): Collection
    {
        return Setting::isFeatureEnabled('faq') ? FaqItem::activeInOrder()->get() : collect();
    }

    /**
     * When the stream starts.
     */
    #[Computed]
    public function streamStartTime(): ?CarbonImmutable
    {
        return Setting::getDate('stream_start_time');
    }

    /**
     * When registration closes.
     */
    #[Computed]
    public function registrationEndTime(): ?CarbonImmutable
    {
        return Setting::getDate('registration_end_time');
    }
}; ?>

@php
    $orgName = Setting::getDisplayValue('org_name', 'DCTerra');
    $eventTitle = Setting::getDisplayValue('event_title', '24U Livestream voor het Goede Doel');
    $eventDescription = Setting::getDisplayValue('event_description', "Doe mee met ons 24-uurs livestream evenement, waarbij we de studenten samenbrengen om {$orgName} te steunen en een echt verschil te maken.");
    $eventVenue = Setting::getDisplayValue('event_venue', 'Locatie volgt binnenkort');
    $expectedParticipants = Setting::getDisplayValue('stat_expected_participants', '100+');
    $fundsGoal = Setting::getDisplayValue('stat_funds_goal_amount', '€2.500+');

    $registrationEnabled = $this->featureEnabled('registration');
    $scheduleEnabled = $this->featureEnabled('schedule');
@endphp

<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-border/40">
        <div class="bg-mesh absolute inset-0"></div>
        <div class="bg-grid absolute inset-0 opacity-60"></div>

        <div class="relative container mx-auto px-4 py-24 md:py-32">
            <div class="animate-fade-in mx-auto max-w-4xl text-center">
                <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
                    <flux:icon.signal class="size-4 animate-pulse" />
                    <span>Live voor {{ $orgName }}</span>
                </div>

                <h1 class="mb-6 text-5xl font-bold tracking-tight text-balance md:text-7xl lg:text-8xl">
                    <span class="text-glow text-primary">{{ $eventTitle }}</span>
                </h1>

                <p class="mx-auto mb-12 max-w-2xl text-xl text-pretty text-muted-foreground">{{ $eventDescription }}</p>

                @if ($registrationEnabled || $scheduleEnabled)
                    <div class="mb-16 flex flex-col justify-center gap-4 sm:flex-row">
                        @if ($registrationEnabled)
                            <x-ui.button size="lg" :href="route('register')" class="px-8 text-lg shadow-lg shadow-primary/30" wire:navigate>
                                <flux:icon.users class="size-5" />
                                <span>Registreer Nu</span>
                                <flux:icon.arrow-right class="size-5" />
                            </x-ui.button>
                        @endif
                        @if ($scheduleEnabled)
                            <x-ui.button size="lg" variant="outline" :href="route('schedule')" class="bg-background/50 px-8 text-lg backdrop-blur" wire:navigate>
                                <flux:icon.play class="size-5" />
                                <span>Bekijk Schema</span>
                            </x-ui.button>
                        @endif
                    </div>
                @endif

                @if ($this->streamStartTime && $this->registrationEndTime)
                    <x-event-timer
                        :stream-start-time="$this->streamStartTime"
                        :registration-end-time="$this->registrationEndTime"
                        :registration-open="$registrationEnabled && Setting::getBoolean('registration_open')"
                    />
                @endif
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="mx-auto grid max-w-4xl grid-cols-1 gap-8 md:grid-cols-3">
                @foreach ([
                    ['icon' => 'clock', 'label' => 'Uur Content', 'counter' => 24],
                    ['icon' => 'users', 'label' => 'Verwachte Deelnemers', 'value' => $expectedParticipants],
                    ['icon' => 'heart', 'label' => 'Inzameldoel', 'value' => $fundsGoal],
                ] as $index => $stat)
                    <x-reveal :delay="$index * 0.08">
                        <x-ui.card class="glow-card bg-card/60 text-center backdrop-blur">
                            <x-ui.card.content class="pt-6">
                                <div class="mb-4 inline-flex size-16 items-center justify-center rounded-2xl bg-primary/10">
                                    <flux:icon :name="$stat['icon']" class="size-8 text-primary" />
                                </div>
                                <h3 class="text-glow mb-2 text-4xl font-bold text-primary">
                                    @isset($stat['counter'])
                                        <x-animated-counter :value="$stat['counter']" />
                                    @else
                                        {{ $stat['value'] }}
                                    @endisset
                                </h3>
                                <p class="text-muted-foreground">{{ $stat['label'] }}</p>
                            </x-ui.card.content>
                        </x-ui.card>
                    </x-reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Event information --}}
    <section class="bg-muted/20 py-16">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-4xl">
                <x-reveal class="mb-12 text-center">
                    <h2 class="mb-4 text-3xl font-bold md:text-4xl">Evenement Informatie</h2>
                    <p class="text-xl text-muted-foreground">Alles wat je moet weten over onze 24U livestream</p>
                </x-reveal>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    @foreach ([
                        ['icon' => 'map-pin', 'title' => 'Locatie', 'lines' => [$eventVenue, 'Live streaming setup met professionele apparatuur']],
                        ['icon' => 'calendar', 'title' => 'Wanneer', 'lines' => ['24 uur continue streaming', 'Meerdere activiteiten en entertainment segmenten']],
                        ['icon' => 'users', 'title' => 'Deelname', 'lines' => ['Kies ervoor om voor de camera te staan of bij het team te horen', 'Vrijwilligers en organisatie welkom']],
                        ['icon' => 'trophy', 'title' => 'Impact', 'lines' => ["Alle opbrengsten gaan naar {$orgName} en het goede doel"]],
                    ] as $index => $card)
                        <x-reveal :delay="$index * 0.08">
                            <x-ui.card class="glow-card h-full">
                                <x-ui.card.content>
                                    <div class="mb-4 inline-flex size-12 items-center justify-center rounded-xl bg-primary/10">
                                        <flux:icon :name="$card['icon']" class="size-6 text-primary" />
                                    </div>
                                    <h3 class="mb-2 text-xl font-semibold">{{ $card['title'] }}</h3>
                                    <p class="text-muted-foreground">
                                        @foreach ($card['lines'] as $line)
                                            {{ $line }}@if (! $loop->last)<br>@endif
                                        @endforeach
                                    </p>
                                </x-ui.card.content>
                            </x-ui.card>
                        </x-reveal>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    @if ($this->faqItems->isNotEmpty())
        <section class="py-16">
            <div class="container mx-auto px-4">
                <div class="mx-auto max-w-3xl">
                    <x-reveal class="mb-12 text-center">
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
                            <flux:icon.question-mark-circle class="size-4" />
                            <span>Veelgestelde Vragen</span>
                        </div>
                        <h2 class="text-3xl font-bold md:text-4xl">Vragen & Antwoorden</h2>
                    </x-reveal>

                    <x-reveal :delay="0.1">
                        <x-ui.card class="glow-card py-2">
                            <div class="px-2 sm:px-4" x-data="{ open: null }">
                                @foreach ($this->faqItems as $faqItem)
                                    <div class="border-b last:border-b-0" wire:key="faq-{{ $faqItem->id }}">
                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between gap-4 px-2 py-4 text-left text-sm font-medium hover:underline"
                                            x-on:click="open = open === {{ $faqItem->id }} ? null : {{ $faqItem->id }}"
                                            :aria-expanded="open === {{ $faqItem->id }}"
                                        >
                                            {{ $faqItem->question }}
                                            <flux:icon.chevron-down class="size-4 shrink-0 text-muted-foreground transition-transform" ::class="open === {{ $faqItem->id }} && 'rotate-180'" />
                                        </button>
                                        <div x-show="open === {{ $faqItem->id }}" x-collapse x-cloak>
                                            <p class="px-2 pb-4 text-sm whitespace-pre-line text-muted-foreground">{{ $faqItem->answer }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.card>
                    </x-reveal>
                </div>
            </div>
        </section>
    @endif

    {{-- Call to action --}}
    @if ($registrationEnabled)
        <section class="relative overflow-hidden border-t border-border/40 py-20">
            <div class="bg-mesh absolute inset-0 opacity-70"></div>
            <div class="relative container mx-auto px-4 text-center">
                <x-reveal class="mx-auto max-w-2xl">
                    <h2 class="text-glow mb-4 text-3xl font-bold text-primary md:text-5xl">Klaar om het Verschil te Maken?</h2>
                    <p class="mb-8 text-xl text-muted-foreground">Doe mee in deze geweldige 24-uurs reis om {{ $orgName }} te steunen</p>
                    <x-ui.button size="lg" :href="route('register')" class="px-8 text-lg shadow-lg shadow-primary/30" wire:navigate>
                        <flux:icon.users class="size-5" />
                        <span>Registreer Nu</span>
                        <flux:icon.arrow-right class="size-5" />
                    </x-ui.button>
                </x-reveal>
            </div>
        </section>
    @endif
</div>
