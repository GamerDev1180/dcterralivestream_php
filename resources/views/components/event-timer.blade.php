{{--
    Two countdown cards: until the stream starts and until registration closes.
    The counting happens in the browser with Alpine.js.
--}}
@props([
    'streamStartTime',
    'registrationEndTime',
    'registrationOpen' => false,
])

@php
    $units = ['days' => 'Dagen', 'hours' => 'Uren', 'minutes' => 'Minuten', 'seconds' => 'Seconden'];

    // Used to hide the wrong block before Alpine has started, so nothing flickers.
    $streamStartedOnLoad = $streamStartTime->isPast();
    $registrationClosedOnLoad = ! $registrationOpen || $registrationEndTime->isPast();
@endphp

<div
    x-data="{
        now: Date.now(),
        streamStart: {{ $streamStartTime->getTimestampMs() }},
        registrationEnd: {{ $registrationEndTime->getTimestampMs() }},
        registrationOpen: @js((bool) $registrationOpen),
        parts(target) {
            const distance = Math.max(0, target - this.now);
            return {
                days: Math.floor(distance / 86400000),
                hours: Math.floor(distance / 3600000) % 24,
                minutes: Math.floor(distance / 60000) % 60,
                seconds: Math.floor(distance / 1000) % 60,
            };
        },
        get streamStarted() { return this.now >= this.streamStart },
        get registrationClosed() { return ! this.registrationOpen || this.now >= this.registrationEnd },
    }"
    x-init="setInterval(() => now = Date.now(), 1000)"
    {{ $attributes->class('mx-auto grid max-w-3xl gap-6 text-left md:grid-cols-2') }}
>
    {{-- Stream start --}}
    <x-ui.card class="glow-card bg-card/70 backdrop-blur">
        <x-ui.card.header>
            <x-ui.card.title class="flex items-center gap-2 text-lg">
                <flux:icon.calendar class="size-5 text-primary" />
                Stream Begint Over
            </x-ui.card.title>
        </x-ui.card.header>
        <x-ui.card.content>
            <div x-show="streamStarted" @if (! $streamStartedOnLoad) x-cloak @endif class="text-center">
                <span class="animate-glow-pulse inline-flex rounded-md bg-success px-4 py-2 text-lg font-medium text-success-foreground">🔴 LIVE NU</span>
                <p class="mt-2 text-sm text-muted-foreground">De 24U livestream is nu bezig!</p>
            </div>
            <div x-show="! streamStarted" @if ($streamStartedOnLoad) x-cloak @endif class="grid grid-cols-4 gap-2 text-center">
                @foreach ($units as $unit => $label)
                    <div class="rounded-lg border border-border/60 bg-background/60 p-3">
                        <div class="text-glow text-3xl font-bold text-primary" x-text="parts(streamStart).{{ $unit }}">0</div>
                        <div class="text-xs text-muted-foreground">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </x-ui.card.content>
    </x-ui.card>

    {{-- Registration end --}}
    <x-ui.card class="glow-card bg-card/70 backdrop-blur">
        <x-ui.card.header>
            <x-ui.card.title class="flex items-center gap-2 text-lg">
                <flux:icon.users class="size-5 text-chart-3" />
                Registratie Sluit Over
            </x-ui.card.title>
        </x-ui.card.header>
        <x-ui.card.content>
            <div x-show="registrationClosed" @if (! $registrationClosedOnLoad) x-cloak @endif class="text-center">
                <x-ui.badge variant="destructive" class="px-4 py-2 text-lg">Registratie Gesloten</x-ui.badge>
                <p class="mt-2 text-sm text-muted-foreground">De registratieperiode is beëindigd</p>
            </div>
            <div x-show="! registrationClosed" @if ($registrationClosedOnLoad) x-cloak @endif class="grid grid-cols-4 gap-2 text-center">
                @foreach ($units as $unit => $label)
                    <div class="rounded-lg border border-border/60 bg-background/60 p-3">
                        <div class="text-3xl font-bold text-chart-3" x-text="parts(registrationEnd).{{ $unit }}">0</div>
                        <div class="text-xs text-muted-foreground">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </x-ui.card.content>
    </x-ui.card>
</div>
