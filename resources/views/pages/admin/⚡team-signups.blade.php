<?php

use App\Enums\TeamSignupStatus;
use App\Enums\TeamSignupType;
use App\Models\TeamSignup;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Team Aanmeldingen')] class extends Component {
    public string $search = '';
    public string $statusFilter = 'all';
    public string $typeFilter = 'all';

    public ?int $selectedSignupId = null;

    /**
     * All signups, newest first.
     *
     * @return Collection<int, TeamSignup>
     */
    #[Computed]
    public function signups(): Collection
    {
        return TeamSignup::latest()->get();
    }

    /**
     * The signups that match the search and filters.
     *
     * @return Collection<int, TeamSignup>
     */
    #[Computed]
    public function filteredSignups(): Collection
    {
        $search = mb_strtolower($this->search);

        return $this->signups->filter(function (TeamSignup $signup) use ($search) {
            $searchableText = mb_strtolower(implode(' ', [$signup->name, $signup->email, $signup->organization_name, $signup->discord_name]));

            return str_contains($searchableText, $search)
                && ($this->statusFilter === 'all' || $signup->status->value === $this->statusFilter)
                && ($this->typeFilter === 'all' || $signup->type->value === $this->typeFilter);
        });
    }

    /**
     * The signup shown in the details dialog.
     */
    #[Computed]
    public function selectedSignup(): ?TeamSignup
    {
        return $this->selectedSignupId ? TeamSignup::find($this->selectedSignupId) : null;
    }

    /**
     * Open the details dialog of a signup.
     */
    public function showDetails(int $signupId): void
    {
        $this->selectedSignupId = $signupId;

        Flux::modal('signup-details')->show();
    }

    /**
     * Approve or reject a signup.
     */
    public function updateStatus(int $signupId, string $status): void
    {
        TeamSignup::findOrFail($signupId)->update([
            'status' => TeamSignupStatus::from($status),
        ]);
    }

    /**
     * Delete a signup.
     */
    public function delete(int $signupId): void
    {
        TeamSignup::findOrFail($signupId)->delete();
    }
}; ?>

<x-ui.card>
    <x-ui.card.header>
        <x-ui.card.title>Team Aanmeldingen</x-ui.card.title>
        <x-ui.card.description>Vrijwilligers en organisaties die zich hebben aangemeld ({{ $this->signups->count() }} totaal)</x-ui.card.description>
    </x-ui.card.header>

    <x-ui.card.content>
        {{-- Filters --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.200ms="search" icon="magnifying-glass" placeholder="Zoek op naam, e-mail of organisatie..." />
            </div>
            <flux:select wire:model.live="typeFilter" class="sm:w-[180px]!">
                <flux:select.option value="all">Alle types</flux:select.option>
                @foreach (TeamSignupType::cases() as $type)
                    <flux:select.option :value="$type->value">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="statusFilter" class="sm:w-[150px]!">
                <flux:select.option value="all">Alle statussen</flux:select.option>
                @foreach (TeamSignupStatus::cases() as $status)
                    <flux:select.option :value="$status->value">{{ $status->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Table --}}
        <div class="rounded-md border px-2">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Naam</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>E-mail</flux:table.column>
                    <flux:table.column>Discord naam</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Aangemeld op</flux:table.column>
                    <flux:table.column align="end">Acties</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->filteredSignups as $signup)
                        <flux:table.row :key="$signup->id">
                            <flux:table.cell variant="strong">
                                {{ $signup->name }}
                                @if ($signup->organization_name)
                                    <div class="text-xs text-muted-foreground">{{ $signup->organization_name }}</div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <x-ui.badge :variant="$signup->type === TeamSignupType::Organisation ? 'default' : 'secondary'" :icon="$signup->type->icon()">
                                    {{ $signup->type->label() }}
                                </x-ui.badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $signup->email }}</flux:table.cell>
                            <flux:table.cell>{{ $signup->discord_name }}</flux:table.cell>
                            <flux:table.cell>
                                <x-ui.badge :variant="$signup->status->badgeVariant()">{{ $signup->status->label() }}</x-ui.badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $signup->created_at->format('j-n-Y') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button wire:click="showDetails({{ $signup->id }})" variant="ghost" size="sm" icon="eye" aria-label="Bekijken" />

                                    @if ($signup->status === TeamSignupStatus::Pending)
                                        <flux:button wire:click="updateStatus({{ $signup->id }}, 'approved')" variant="ghost" size="sm" aria-label="Goedkeuren">
                                            <flux:icon.check-circle class="size-4 text-green-600" />
                                        </flux:button>
                                        <flux:button wire:click="updateStatus({{ $signup->id }}, 'rejected')" variant="ghost" size="sm" aria-label="Afwijzen">
                                            <flux:icon.x-circle class="size-4 text-red-600" />
                                        </flux:button>
                                    @endif

                                    <flux:button wire:click="delete({{ $signup->id }})" wire:confirm="Weet je zeker dat je deze aanmelding wilt verwijderen?" variant="ghost" size="sm" aria-label="Verwijderen">
                                        <flux:icon.trash class="size-4 text-red-600" />
                                    </flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->filteredSignups->isEmpty())
            <div class="py-8 text-center text-muted-foreground">Geen aanmeldingen gevonden.</div>
        @endif
    </x-ui.card.content>

    {{-- Details dialog --}}
    <flux:modal name="signup-details" class="w-full max-w-2xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Aanmelding Details</flux:heading>
                <flux:text>Volledige informatie van de aanmelding</flux:text>
            </div>

            @if ($signup = $this->selectedSignup)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Naam</label>
                        <p>{{ $signup->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Type</label>
                        <p>{{ $signup->type->label() }}</p>
                    </div>
                    @if ($signup->organization_name)
                        <div>
                            <label class="text-sm font-medium">Organisatie</label>
                            <p>{{ $signup->organization_name }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="text-sm font-medium">E-mail</label>
                        <p>{{ $signup->email }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Telefoon</label>
                        <p>{{ $signup->phone ?: '-' }}</p>
                    </div>
                </div>

                @if ($signup->message)
                    <div>
                        <label class="text-sm font-medium">Bericht</label>
                        <p class="mt-1 rounded border p-3 text-sm">{{ $signup->message }}</p>
                    </div>
                @endif
            @endif
        </div>
    </flux:modal>
</x-ui.card>
