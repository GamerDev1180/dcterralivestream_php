<?php

use App\Enums\ParticipationType;
use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    /**
     * The numbers shown in the stat cards at the top of the admin dashboard.
     *
     * @return array<int, array{label: string, icon: string, value: int}>
     */
    #[Computed]
    public function stats(): array
    {
        return [
            ['label' => 'Total Registrations', 'icon' => 'users', 'value' => Registration::count()],
            ['label' => 'On Camera', 'icon' => 'video-camera', 'value' => Registration::where('participation_type', ParticipationType::OnCamera)->count()],
            ['label' => 'Behind Scenes', 'icon' => 'video-camera-slash', 'value' => Registration::where('participation_type', ParticipationType::OffCamera)->count()],
            ['label' => 'Confirmed', 'icon' => 'user-plus', 'value' => Registration::where('status', RegistrationStatus::Confirmed)->count()],
            ['label' => 'Pending', 'icon' => 'calendar', 'value' => Registration::where('status', RegistrationStatus::Pending)->count()],
        ];
    }

    /**
     * Recalculate the numbers when a registration was changed on another component.
     */
    #[On('registrations-changed')]
    public function refreshStats(): void
    {
        unset($this->stats);
    }
}; ?>

<div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
    @foreach ($this->stats as $stat)
        <x-ui.card>
            <x-ui.card.header class="flex flex-row items-center justify-between pb-2">
                <x-ui.card.title class="text-sm font-medium">{{ $stat['label'] }}</x-ui.card.title>
                <flux:icon :name="$stat['icon']" class="size-4 text-muted-foreground" />
            </x-ui.card.header>
            <x-ui.card.content>
                <div class="text-2xl font-bold" wire:key="stat-{{ $stat['label'] }}-{{ $stat['value'] }}">
                    <x-animated-counter :value="$stat['value']" :duration="0.6" />
                </div>
            </x-ui.card.content>
        </x-ui.card>
    @endforeach
</div>
