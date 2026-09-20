<?php

use App\Enums\SponsorTier;
use App\Models\Sponsor;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Sponsoren')] class extends Component {
    /** The id of the sponsor being edited, or null when adding a new one. */
    public ?int $editingId = null;

    public string $name = '';
    public string $tier = 'Silver';
    public string $logo_url = '';
    public string $description = '';
    public string $website = '';
    public string $contribution = '';
    public int $display_order = 0;
    public bool $is_active = true;

    /**
     * All sponsors, including inactive ones.
     *
     * @return Collection<int, Sponsor>
     */
    #[Computed]
    public function sponsors(): Collection
    {
        return Sponsor::orderBy('display_order')->orderBy('name')->get();
    }

    /**
     * Open an empty "Add New Sponsor" dialog.
     */
    public function create(): void
    {
        $this->resetForm();

        Flux::modal('sponsor-form')->show();
    }

    /**
     * Open the dialog filled with an existing sponsor.
     */
    public function edit(int $sponsorId): void
    {
        $sponsor = Sponsor::findOrFail($sponsorId);

        $this->resetForm();
        $this->editingId = $sponsor->id;
        $this->name = $sponsor->name;
        $this->tier = $sponsor->tier->value;
        $this->logo_url = $sponsor->logo_url ?? '';
        $this->description = $sponsor->description ?? '';
        $this->website = $sponsor->website ?? '';
        $this->contribution = $sponsor->contribution ?? '';
        $this->display_order = $sponsor->display_order;
        $this->is_active = $sponsor->is_active;

        Flux::modal('sponsor-form')->show();
    }

    /**
     * Add or update the sponsor.
     */
    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Sponsor::class)->ignore($this->editingId)],
            'tier' => ['required', Rule::enum(SponsorTier::class)],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'string', 'max:500'],
            'contribution' => ['nullable', 'string', 'max:255'],
            'display_order' => ['integer'],
            'is_active' => ['boolean'],
        ]);

        foreach (['logo_url', 'description', 'website', 'contribution'] as $optionalField) {
            $validated[$optionalField] = $validated[$optionalField] ?: null;
        }

        if ($this->editingId) {
            Sponsor::findOrFail($this->editingId)->update($validated);
        } else {
            Sponsor::create($validated);
        }

        Flux::modal('sponsor-form')->close();
        Flux::toast(variant: 'success', heading: 'Success', text: 'Sponsor '.($this->editingId ? 'updated' : 'added').' successfully');

        $this->resetForm();
    }

    /**
     * Delete a sponsor.
     */
    public function delete(int $sponsorId): void
    {
        Sponsor::findOrFail($sponsorId)->delete();

        Flux::toast(variant: 'success', heading: 'Success', text: 'Sponsor deleted successfully');
    }

    /**
     * Empty the form.
     */
    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'tier', 'logo_url', 'description', 'website', 'contribution', 'display_order', 'is_active');
        $this->resetErrorBag();
    }
}; ?>

<x-ui.card>
    <x-ui.card.header>
        <div class="flex items-center justify-between">
            <x-ui.card.title>Sponsors Management</x-ui.card.title>
            <flux:button wire:click="create" variant="primary" icon="plus">Add Sponsor</flux:button>
        </div>
    </x-ui.card.header>

    <x-ui.card.content>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Tier</flux:table.column>
                <flux:table.column>Contribution</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->sponsors as $sponsor)
                    <flux:table.row :key="$sponsor->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                @if ($sponsor->logo_url)
                                    <img src="{{ $sponsor->logo_url }}" alt="{{ $sponsor->name }}" class="size-8 object-contain">
                                @endif
                                <span class="font-medium">{{ $sponsor->name }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <x-ui.badge class="{{ $sponsor->tier->adminBadgeClasses() }}">{{ $sponsor->tier->value }}</x-ui.badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $sponsor->contribution ?: '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <x-ui.badge :variant="$sponsor->is_active ? 'default' : 'secondary'">{{ $sponsor->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                @if ($sponsor->website)
                                    <flux:button :href="$sponsor->website" target="_blank" variant="ghost" size="sm" icon="arrow-top-right-on-square" aria-label="Website" />
                                @endif
                                <flux:button wire:click="edit({{ $sponsor->id }})" variant="ghost" size="sm" icon="pencil-square" aria-label="Edit" />
                                <flux:button wire:click="delete({{ $sponsor->id }})" wire:confirm="Are you sure you want to delete this sponsor?" variant="ghost" size="sm" icon="trash" aria-label="Delete" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-8 text-center text-muted-foreground">No sponsors yet.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </x-ui.card.content>

    <flux:modal name="sponsor-form" class="w-full max-w-2xl">
        <form wire:submit="save" class="space-y-4">
            <flux:heading size="lg">{{ $editingId ? 'Edit Sponsor' : 'Add New Sponsor' }}</flux:heading>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="name" label="Name *" required />
                <flux:select wire:model="tier" label="Tier *">
                    @foreach (SponsorTier::cases() as $sponsorTier)
                        <flux:select.option :value="$sponsorTier->value">{{ $sponsorTier->value }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:input wire:model="logo_url" label="Logo URL" placeholder="/path/to/logo.png" />
            <flux:textarea wire:model="description" label="Description" rows="3" />

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="website" label="Website" placeholder="https://example.com" />
                <flux:input wire:model="display_order" label="Display Order" type="number" />
            </div>

            <flux:input wire:model="contribution" label="Contribution" placeholder="What they're contributing" />
            <flux:switch wire:model="is_active" label="Active" align="left" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Update' : 'Add' }} Sponsor</flux:button>
            </div>
        </form>
    </flux:modal>
</x-ui.card>
