<?php

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\ScheduleEvent;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')] #[Title('Schema')] class extends Component {
    /**
     * The active programme items in chronological order.
     *
     * @return Collection<int, ScheduleEvent>
     */
    #[Computed]
    public function events(): Collection
    {
        return ScheduleEvent::activeInOrder()->get();
    }

    #[Computed]
    public function streamStartTime(): ?CarbonImmutable
    {
        return Setting::getDate('stream_start_time');
    }

    /**
     * Determine if the livestream has started.
     */
    #[Computed]
    public function streamStarted(): bool
    {
        return $this->streamStartTime !== null && now()->gte($this->streamStartTime);
    }

    /**
     * The position of the event that is live right now, or null when nothing is live.
     */
    #[Computed]
    public function currentEventIndex(): ?int
    {
        if (! $this->streamStarted) {
            return null;
        }

        $index = $this->events->search(fn (ScheduleEvent $event) => $event->isLive());

        return $index === false ? null : $index;
    }

    #[Computed]
    public function confirmedParticipants(): int
    {
        return Registration::where('status', RegistrationStatus::Confirmed)->count();
    }
}; ?>

<div wire:poll.60s>
    @if (! Setting::isFeatureEnabled('schedule'))
        <x-feature-unavailable title="Schema is nog niet beschikbaar" text="Kom later terug voor het volledige programma." />
    @else
        {{-- Hero --}}
        <section class="relative overflow-hidden border-b border-border/40">
            <div class="bg-mesh absolute inset-0"></div>
            <div class="relative container mx-auto px-4 py-16">
                <div class="mx-auto max-w-4xl text-center">
                    <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
                        <flux:icon.clock class="size-4" />
                        <span>Live Programma</span>
                    </div>

                    <h1 class="text-glow mb-6 text-4xl font-bold text-balance text-primary md:text-6xl">24U Livestream Schema</h1>

                    <p class="mx-auto mb-8 max-w-2xl text-xl text-pretty text-muted-foreground">
                        Volg ons volledige 24-uursschema met realtime updates. Bekijk wat er nu speelt en wat er hierna komt!
                    </p>

                    <div class="flex items-center justify-center gap-4 text-lg">
                        <div class="flex items-center gap-2">
                            <flux:icon.clock class="size-5 text-primary" />
                            <span
                                class="font-mono"
                                x-data="{ time: '' }"
                                x-init="const tick = () => time = new Date().toLocaleTimeString('nl-NL', { hour12: false }); tick(); setInterval(tick, 1000)"
                                x-text="time"
                            >{{ now()->format('H:i:s') }}</span>
                        </div>

                        @if ($this->streamStarted && $this->currentEventIndex !== null)
                            <x-ui.badge class="animate-glow-pulse" icon="play">NU LIVE</x-ui.badge>
                        @elseif (! $this->streamStarted)
                            <x-ui.badge variant="secondary" icon="calendar">GEPLAND</x-ui.badge>
                        @endif
                    </div>

                    @if ($this->streamStartTime)
                        <div class="mt-6 inline-block rounded-lg border border-border/60 bg-card/60 p-4 backdrop-blur">
                            <p class="text-sm text-muted-foreground">
                                @if ($this->streamStarted)
                                    <span class="font-medium text-primary">🔴 Stream is nu LIVE!</span>
                                @else
                                    Stream begint:
                                    <span class="font-medium text-foreground">{{ $this->streamStartTime->locale('nl')->translatedFormat('l j F \o\m H:i') }}</span>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Schedule --}}
        <section class="py-16">
            <div class="container mx-auto px-4">
                <div class="mx-auto max-w-4xl">
                    @if ($this->events->isEmpty())
                        <p class="text-center text-muted-foreground">Het schema wordt binnenkort bekendgemaakt.</p>
                    @else
                        <div class="space-y-6">
                            @foreach ($this->events as $index => $event)
                                @php
                                    $isCurrentEvent = $this->streamStarted && $index === $this->currentEventIndex;
                                    $isPastEvent = $this->streamStarted && $this->currentEventIndex !== null && $index < $this->currentEventIndex;
                                    $isFutureEvent = ! $this->streamStarted;
                                @endphp

                                <x-reveal :delay="min($index * 0.05, 0.5)" wire:key="event-{{ $event->id }}">
                                    <x-ui.card :class="Arr::toCssClasses([
                                        'transition-all duration-300',
                                        'glow-card scale-[1.01] bg-primary/5 ring-1 ring-primary' => $isCurrentEvent,
                                        'opacity-50' => $isPastEvent,
                                        'border-dashed' => $isFutureEvent,
                                    ])">
                                        <x-ui.card.header class="pb-3">
                                            <div class="flex items-center gap-3">
                                                <div class="rounded-lg border p-2 {{ $event->event_type->colorClasses() }}">
                                                    <flux:icon :name="$event->event_type->icon()" class="size-5" />
                                                </div>
                                                <div>
                                                    <x-ui.card.title class="flex flex-wrap items-center gap-2">
                                                        <span>{{ $event->title }}</span>
                                                        @if ($isCurrentEvent)
                                                            <x-ui.badge class="animate-glow-pulse">LIVE</x-ui.badge>
                                                        @endif
                                                        @if ($isPastEvent)
                                                            <x-ui.badge variant="secondary">AFGEROND</x-ui.badge>
                                                        @endif
                                                        @if ($isFutureEvent)
                                                            <x-ui.badge variant="outline">GEPLAND</x-ui.badge>
                                                        @endif
                                                    </x-ui.card.title>
                                                    <div class="mt-1 flex items-center gap-4 text-sm text-muted-foreground">
                                                        <div class="flex items-center gap-1">
                                                            <flux:icon.clock class="size-4" />
                                                            <span>{{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }}</span>
                                                        </div>
                                                        <x-ui.badge variant="outline" class="{{ $event->event_type->colorClasses() }}">{{ $event->event_type->label() }}</x-ui.badge>
                                                    </div>
                                                </div>
                                            </div>
                                        </x-ui.card.header>

                                        @if ($event->description || $event->participants)
                                            <x-ui.card.content>
                                                @if ($event->description)
                                                    <p class="mb-4 text-muted-foreground">{{ $event->description }}</p>
                                                @endif
                                                @if ($event->participants)
                                                    <div class="flex items-center gap-2">
                                                        <flux:icon.users class="size-4 text-muted-foreground" />
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach ($event->participants as $participant)
                                                                <x-ui.badge variant="secondary">{{ $participant }}</x-ui.badge>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </x-ui.card.content>
                                        @endif
                                    </x-ui.card>
                                </x-reveal>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Stats --}}
        <section class="bg-muted/20 py-16">
            <div class="container mx-auto px-4">
                <div class="mx-auto max-w-4xl">
                    <h2 class="mb-8 text-center text-2xl font-bold">{{ $this->streamStarted ? 'Live Statistieken' : 'Programma Overzicht' }}</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <x-ui.card class="glow-card">
                            <x-ui.card.content class="pt-6 text-center">
                                <flux:icon.clock class="mx-auto mb-2 size-8 text-primary" />
                                <h3 class="text-glow text-3xl font-bold text-primary">
                                    <x-animated-counter :value="$this->currentEventIndex !== null ? $this->events->count() - $this->currentEventIndex : $this->events->count()" />
                                </h3>
                                <p class="text-muted-foreground">{{ $this->streamStarted ? 'Resterende Onderdelen' : 'Totaal Onderdelen' }}</p>
                            </x-ui.card.content>
                        </x-ui.card>
                        <x-ui.card class="glow-card">
                            <x-ui.card.content class="pt-6 text-center">
                                <flux:icon.users class="mx-auto mb-2 size-8 text-primary" />
                                <h3 class="text-glow text-3xl font-bold text-primary">
                                    <x-animated-counter :value="$this->confirmedParticipants" />
                                </h3>
                                <p class="text-muted-foreground">Bevestigde Deelnemers</p>
                            </x-ui.card.content>
                        </x-ui.card>
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>
