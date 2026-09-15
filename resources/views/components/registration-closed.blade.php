{{-- Shown on the registration page when registration is closed. --}}
@props([
    'streamStartTime' => null,
    'registrationEndTime' => null,
])

<div class="flex min-h-[60vh] items-center justify-center">
    <x-ui.card class="glow-card mx-auto max-w-2xl">
        <x-ui.card.header class="justify-items-center pb-6 text-center">
            <div class="mx-auto mb-4 w-fit rounded-full bg-primary/10 p-3">
                <flux:icon.clock class="size-8 text-primary" />
            </div>
            <x-ui.card.title class="text-3xl font-bold text-balance">Registratieperiode is Afgelopen</x-ui.card.title>
            <x-ui.badge variant="destructive" class="mx-auto px-4 py-2 text-lg">Registratie Gesloten</x-ui.badge>
        </x-ui.card.header>

        <x-ui.card.content class="space-y-6 text-center">
            <p class="text-lg text-pretty text-muted-foreground">Helaas is de registratieperiode voor de 24U Livestream gesloten.</p>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-border/60 bg-background/50 p-4">
                    <div class="mb-2 flex items-center gap-2">
                        <flux:icon.calendar class="size-4 text-muted-foreground" />
                        <span class="font-medium">Registratie Gesloten</span>
                    </div>
                    <p class="text-sm text-muted-foreground">{{ $registrationEndTime?->locale('nl')->translatedFormat('l j F Y \o\m H:i') ?? 'Onbekend' }}</p>
                </div>

                <div class="rounded-lg border border-border/60 bg-background/50 p-4">
                    <div class="mb-2 flex items-center gap-2">
                        <flux:icon.calendar class="size-4 text-primary" />
                        <span class="font-medium">Stream Begint</span>
                    </div>
                    <p class="text-sm text-muted-foreground">{{ $streamStartTime?->locale('nl')->translatedFormat('l j F Y \o\m H:i') ?? 'Binnenkort bekend' }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-border/50 bg-background/30 p-6">
                <h3 class="mb-2 font-semibold">Toch nog steunen?</h3>
                <p class="mb-4 text-sm text-muted-foreground">
                    Ook al is de registratie gesloten, je kunt ons nog steunen door de livestream te bekijken en te delen met anderen.
                </p>
                <div class="flex flex-col justify-center gap-3 sm:flex-row">
                    <x-ui.button variant="outline" :href="route('schedule')" wire:navigate>
                        <flux:icon.calendar class="size-4" />
                        Bekijk Schema
                    </x-ui.button>
                    <x-ui.button :href="route('home')" wire:navigate>
                        <flux:icon.arrow-left class="size-4" />
                        Terug naar Home
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card.content>
    </x-ui.card>
</div>
