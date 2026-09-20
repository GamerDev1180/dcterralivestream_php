<?php

use App\Actions\ArchiveAndResetEvent;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Systeem')] class extends Component {
    public string $resetMessage = '';

    /**
     * Archive all registrations and team signups and clear them for next year. Only super admins may do this.
     */
    public function archiveAndReset(ArchiveAndResetEvent $archiveAndResetEvent): void
    {
        if (! Auth::user()->isSuperAdmin()) {
            $this->resetMessage = 'Insufficient permissions';

            return;
        }

        $result = $archiveAndResetEvent->handle();

        $this->dispatch('registrations-changed');
        $this->resetMessage = "Done. Registrations and team signups archived under year {$result['archived_year']}.";
    }
}; ?>

<div class="space-y-6">
    {{-- System information --}}
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title class="flex items-center gap-2">
                <flux:icon.cog-6-tooth class="size-5" />
                <span>System Information</span>
            </x-ui.card.title>
            <x-ui.card.description>Current system configuration and status</x-ui.card.description>
        </x-ui.card.header>

        <x-ui.card.content>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm font-medium">Event Name</p>
                    <p class="text-sm text-muted-foreground">{{ Setting::getValue('event_title') }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium">System Version</p>
                    <p class="text-sm text-muted-foreground">v1.0.0 (Laravel {{ app()->version() }})</p>
                </div>
                <div>
                    <p class="text-sm font-medium">Database Status</p>
                    <p class="text-sm text-green-600">Connected ({{ config('database.default') }})</p>
                </div>
                <div>
                    <p class="text-sm font-medium">New Admins</p>
                    <p class="text-sm text-muted-foreground">Run <code>php artisan app:create-admin</code></p>
                </div>
                <div>
                    <p class="text-sm font-medium">Email Service</p>
                    <p class="text-sm text-green-600">Configured ({{ config('mail.default') }})</p>
                </div>
            </div>
        </x-ui.card.content>
    </x-ui.card>

    {{-- Archive & reset --}}
    <x-ui.card class="border-destructive/40">
        <x-ui.card.header>
            <x-ui.card.title class="flex items-center gap-2">
                <flux:icon.arrow-path class="size-5" />
                <span>Archive & Reset for Next Year</span>
            </x-ui.card.title>
            <x-ui.card.description>
                Archives every registration and team signup, then clears them so the site is ready for the next event cycle.
                Sponsors, questions, schedule, and site settings are left untouched. Requires super admin.
            </x-ui.card.description>
        </x-ui.card.header>

        <x-ui.card.content class="space-y-4">
            @if ($resetMessage)
                <x-ui.alert>{{ $resetMessage }}</x-ui.alert>
            @endif

            <flux:button
                wire:click="archiveAndReset"
                wire:confirm="This will archive ALL current registrations and team signups, then clear them so you can start fresh for next year's event. Sponsors, questions, and site settings are kept. This cannot be undone from the UI. Continue?"
                variant="danger"
                icon="arrow-path"
            >
                Archive & Reset Registrations
            </flux:button>
        </x-ui.card.content>
    </x-ui.card>
</div>
