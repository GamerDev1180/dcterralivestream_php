<?php

use App\Models\EmailDomain;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('E-maildomeinen')] class extends Component {
    public string $newDomain = '';

    public string $success = '';

    /**
     * All allowed domains, newest first.
     *
     * @return Collection<int, EmailDomain>
     */
    #[Computed]
    public function domains(): Collection
    {
        return EmailDomain::latest()->get();
    }

    /**
     * Add a new allowed domain (e.g. "@student.dcterra.nl").
     */
    public function addDomain(): void
    {
        $this->success = '';
        $this->newDomain = strtolower(trim($this->newDomain));

        $this->validate([
            'newDomain' => ['required', 'string', 'min:2', 'max:255', 'starts_with:@', Rule::unique(EmailDomain::class, 'domain')],
        ], [
            'newDomain.required' => 'Please enter a domain',
            'newDomain.starts_with' => 'Domain must start with @',
            'newDomain.unique' => 'Domain already exists',
        ]);

        EmailDomain::create(['domain' => $this->newDomain]);

        $this->reset('newDomain');
        $this->success = 'Domain added successfully';
    }

    /**
     * Remove an allowed domain.
     */
    public function deleteDomain(int $domainId): void
    {
        EmailDomain::findOrFail($domainId)->delete();

        $this->resetErrorBag();
        $this->success = 'Domain deleted successfully';
    }
}; ?>

<x-ui.card>
    <x-ui.card.header>
        <x-ui.card.title class="flex items-center gap-2">
            <flux:icon.envelope class="size-5" />
            <span>Allowed Email Domains</span>
        </x-ui.card.title>
        <x-ui.card.description>Manage which email domains are allowed for registration</x-ui.card.description>
    </x-ui.card.header>

    <x-ui.card.content class="space-y-6">
        {{-- Add new domain --}}
        <div class="space-y-4">
            <form wire:submit="addDomain" class="flex gap-2">
                <div class="flex-1">
                    <flux:input wire:model="newDomain" label="Add New Domain" placeholder="@example.com" error:class="hidden" />
                </div>
                <div class="flex items-end">
                    <flux:button type="submit" variant="primary" icon="plus">Add Domain</flux:button>
                </div>
            </form>

            @error('newDomain')
                <x-ui.alert variant="destructive">{{ $message }}</x-ui.alert>
            @enderror

            @if ($success)
                <x-ui.alert>{{ $success }}</x-ui.alert>
            @endif
        </div>

        {{-- Domains --}}
        <div class="rounded-md border px-2">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Domain</flux:table.column>
                    <flux:table.column>Added Date</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->domains as $domain)
                        <flux:table.row :key="$domain->id">
                            <flux:table.cell><code class="rounded bg-muted px-2 py-1 text-sm">{{ $domain->domain }}</code></flux:table.cell>
                            <flux:table.cell>{{ $domain->created_at->format('d-m-Y') }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button wire:click="deleteDomain({{ $domain->id }})" wire:confirm="Are you sure you want to delete this domain?" variant="ghost" size="sm" aria-label="Delete">
                                    <flux:icon.trash class="size-4 text-red-600" />
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->domains->isEmpty())
            <div class="py-8 text-center text-muted-foreground">No email domains configured.</div>
        @endif
    </x-ui.card.content>
</x-ui.card>
