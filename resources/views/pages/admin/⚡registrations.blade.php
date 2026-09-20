<?php

use App\Enums\ParticipationType;
use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Registraties')] class extends Component {
    public string $search = '';
    public string $statusFilter = 'all';
    public string $cameraFilter = 'all';

    public ?int $selectedRegistrationId = null;

    /**
     * All registrations, newest first.
     *
     * @return Collection<int, Registration>
     */
    #[Computed]
    public function registrations(): Collection
    {
        return Registration::latest()->get();
    }

    /**
     * The registrations that match the search and filters.
     *
     * @return Collection<int, Registration>
     */
    #[Computed]
    public function filteredRegistrations(): Collection
    {
        $search = mb_strtolower($this->search);

        return $this->registrations->filter(function (Registration $registration) use ($search) {
            $matchesSearch = str_contains(mb_strtolower($registration->name), $search)
                || str_contains(mb_strtolower($registration->email), $search);
            $matchesStatus = $this->statusFilter === 'all' || $registration->status->value === $this->statusFilter;
            $matchesCamera = $this->cameraFilter === 'all' || $registration->participation_type->value === $this->cameraFilter;

            return $matchesSearch && $matchesStatus && $matchesCamera;
        });
    }

    /**
     * The registration shown in the details dialog.
     */
    #[Computed]
    public function selectedRegistration(): ?Registration
    {
        return $this->selectedRegistrationId
            ? Registration::with('answers.question')->find($this->selectedRegistrationId)
            : null;
    }

    /**
     * Open the details dialog of a registration.
     */
    public function showDetails(int $registrationId): void
    {
        $this->selectedRegistrationId = $registrationId;

        Flux::modal('registration-details')->show();
    }

    /**
     * Confirm or cancel a registration.
     */
    public function updateStatus(int $registrationId, string $status): void
    {
        Registration::findOrFail($registrationId)->update([
            'status' => RegistrationStatus::from($status),
        ]);

        $this->dispatch('registrations-changed');
    }

    /**
     * Delete a registration and its answers.
     */
    public function delete(int $registrationId): void
    {
        Registration::findOrFail($registrationId)->delete();

        $this->dispatch('registrations-changed');
    }
}; ?>

<x-ui.card>
    <x-ui.card.header>
        <div class="flex items-center justify-between gap-4">
            <div class="space-y-1.5">
                <x-ui.card.title>Event Registrations</x-ui.card.title>
                <x-ui.card.description>Manage and view all event registrations ({{ $this->registrations->count() }} total)</x-ui.card.description>
            </div>
            <x-ui.button variant="outline" size="sm" :href="route('admin.registrations.export')">
                <flux:icon.arrow-down-tray class="size-4" />
                Export CSV
            </x-ui.button>
        </div>
    </x-ui.card.header>

    <x-ui.card.content>
        {{-- Filters --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.200ms="search" icon="magnifying-glass" placeholder="Search by name or email..." />
            </div>
            <flux:select wire:model.live="statusFilter" class="sm:w-[150px]!">
                <flux:select.option value="all">All Status</flux:select.option>
                @foreach (RegistrationStatus::cases() as $status)
                    <flux:select.option :value="$status->value">{{ $status->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="cameraFilter" class="sm:w-[150px]!">
                <flux:select.option value="all">All Types</flux:select.option>
                @foreach (ParticipationType::cases() as $type)
                    <flux:select.option :value="$type->value">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Table --}}
        <div class="rounded-md border px-2">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Discord Code</flux:table.column>
                    <flux:table.column>Registration Date</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->filteredRegistrations as $registration)
                        <flux:table.row :key="$registration->id">
                            <flux:table.cell variant="strong">{{ $registration->name }}</flux:table.cell>
                            <flux:table.cell>{{ $registration->email }}</flux:table.cell>
                            <flux:table.cell>
                                <x-ui.badge :variant="$registration->participation_type === ParticipationType::OnCamera ? 'default' : 'secondary'" :icon="$registration->participation_type->icon()">
                                    {{ $registration->participation_type->label() }}
                                </x-ui.badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <x-ui.badge :variant="$registration->status->badgeVariant()">{{ $registration->status->value }}</x-ui.badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <code class="rounded bg-muted px-2 py-1 text-xs">{{ $registration->discord_code }}</code>
                            </flux:table.cell>
                            <flux:table.cell>{{ $registration->created_at->format('d-m-Y') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button wire:click="showDetails({{ $registration->id }})" variant="ghost" size="sm" icon="eye" aria-label="View" />

                                    @if ($registration->status === RegistrationStatus::Pending)
                                        <flux:button wire:click="updateStatus({{ $registration->id }}, 'confirmed')" variant="ghost" size="sm" aria-label="Confirm">
                                            <flux:icon.check-circle class="size-4 text-green-600" />
                                        </flux:button>
                                        <flux:button wire:click="updateStatus({{ $registration->id }}, 'cancelled')" variant="ghost" size="sm" aria-label="Cancel">
                                            <flux:icon.x-circle class="size-4 text-red-600" />
                                        </flux:button>
                                    @endif

                                    <flux:button wire:click="delete({{ $registration->id }})" wire:confirm="Are you sure you want to delete this registration?" variant="ghost" size="sm" aria-label="Delete">
                                        <flux:icon.trash class="size-4 text-red-600" />
                                    </flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->filteredRegistrations->isEmpty())
            <div class="py-8 text-center text-muted-foreground">No registrations found matching your filters.</div>
        @endif
    </x-ui.card.content>

    {{-- Details dialog --}}
    <flux:modal name="registration-details" class="w-full max-w-2xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Registration Details</flux:heading>
                <flux:text>View complete registration information</flux:text>
            </div>

            @if ($registration = $this->selectedRegistration)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Name</label>
                        <p>{{ $registration->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Email</label>
                        <p>{{ $registration->email }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Type</label>
                        <p>{{ $registration->participation_type->label() }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Discord Code</label>
                        <p><code>{{ $registration->discord_code }}</code></p>
                    </div>
                </div>

                @if ($registration->answers->isNotEmpty())
                    <div>
                        <label class="text-sm font-medium">Answers</label>
                        <div class="mt-2 space-y-2">
                            @foreach ($registration->answers->sortBy('question.order_index') as $answer)
                                <div class="rounded border p-3">
                                    <p class="text-sm font-medium text-muted-foreground">{{ $answer->question->question_text }}</p>
                                    <p class="mt-1">{{ $answer->answer }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </flux:modal>
</x-ui.card>
