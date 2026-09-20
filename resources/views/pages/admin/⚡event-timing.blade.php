<?php

use App\Models\Setting;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Timing')] class extends Component {
    public bool $registration_open = true;
    public string $stream_start_time = '';
    public string $registration_end_time = '';
    public string $event_title = '';
    public string $event_description = '';

    /**
     * Fill the form with the current settings.
     */
    public function mount(): void
    {
        $this->registration_open = Setting::getBoolean('registration_open');
        $this->stream_start_time = Setting::getDate('stream_start_time')?->format('Y-m-d\TH:i') ?? '';
        $this->registration_end_time = Setting::getDate('registration_end_time')?->format('Y-m-d\TH:i') ?? '';
        $this->event_title = Setting::getValue('event_title');
        $this->event_description = Setting::getValue('event_description');
    }

    /**
     * Every field saves itself as soon as it changes (switch) or loses focus (inputs).
     */
    public function updated(string $property): void
    {
        $this->validateOnly($property, [
            'registration_open' => ['boolean'],
            'stream_start_time' => ['nullable', 'date'],
            'registration_end_time' => ['nullable', 'date'],
            'event_title' => ['string', 'max:255'],
            'event_description' => ['nullable', 'string', 'max:1000'],
        ]);

        $value = $this->{$property};

        if (in_array($property, ['stream_start_time', 'registration_end_time'])) {
            $value = $value ? date('Y-m-d H:i:s', strtotime($value)) : '';
        }

        Setting::setValue($property, $value);

        Flux::toast(variant: 'success', heading: 'Success', text: 'Configuration updated successfully');
    }
}; ?>

@php
    $streamStartTime = Setting::getDate('stream_start_time');
    $registrationEndTime = Setting::getDate('registration_end_time');
    $isStreamLive = $streamStartTime !== null && now()->gte($streamStartTime);
    $isRegistrationClosed = ! $registration_open || ($registrationEndTime !== null && now()->gte($registrationEndTime));
@endphp

<div class="space-y-6">
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title class="flex items-center gap-2">
                <flux:icon.clock class="size-5" />
                Event Timing Configuration
            </x-ui.card.title>
        </x-ui.card.header>

        <x-ui.card.content class="space-y-6">
            {{-- Registration status --}}
            <div class="flex items-center justify-between rounded-lg bg-muted/50 p-4">
                <div>
                    <h3 class="font-medium">Registration Status</h3>
                    <p class="text-sm text-muted-foreground">{{ $isRegistrationClosed ? 'Registration is currently closed' : 'Registration is open' }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <x-ui.badge :variant="$isRegistrationClosed ? 'destructive' : 'default'">{{ $isRegistrationClosed ? 'Closed' : 'Open' }}</x-ui.badge>
                    <flux:switch wire:model.live="registration_open" aria-label="Registration open" />
                </div>
            </div>

            {{-- Stream status --}}
            <div class="flex items-center justify-between rounded-lg bg-muted/50 p-4">
                <div>
                    <h3 class="font-medium">Stream Status</h3>
                    <p class="text-sm text-muted-foreground">{{ $isStreamLive ? 'Stream is currently live' : 'Stream has not started yet' }}</p>
                </div>
                <x-ui.badge :variant="$isStreamLive ? 'default' : 'secondary'">{{ $isStreamLive ? '🔴 LIVE' : 'Scheduled' }}</x-ui.badge>
            </div>

            {{-- Timing --}}
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model.blur="stream_start_time" label="Stream Start Time" type="datetime-local" />
                <flux:input wire:model.blur="registration_end_time" label="Registration End Time" type="datetime-local" />
            </div>

            {{-- Event details --}}
            <div class="space-y-4">
                <flux:input wire:model.blur="event_title" label="Event Title" />
                <flux:input wire:model.blur="event_description" label="Event Description" />
            </div>
        </x-ui.card.content>
    </x-ui.card>
</div>
