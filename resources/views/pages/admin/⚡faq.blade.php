<?php

use App\Models\FaqItem;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('FAQ')] class extends Component {
    /** The id of the item being edited, or null when adding a new one. */
    public ?int $editingId = null;

    public string $question = '';
    public string $answer = '';
    public int $display_order = 0;
    public bool $is_active = true;

    /**
     * All FAQ items.
     *
     * @return Collection<int, FaqItem>
     */
    #[Computed]
    public function items(): Collection
    {
        return FaqItem::orderBy('display_order')->orderBy('id')->get();
    }

    /**
     * Open an empty "Add FAQ Item" dialog.
     */
    public function create(): void
    {
        $this->resetForm();

        Flux::modal('faq-form')->show();
    }

    /**
     * Open the dialog filled with an existing item.
     */
    public function edit(int $faqItemId): void
    {
        $faqItem = FaqItem::findOrFail($faqItemId);

        $this->resetForm();
        $this->editingId = $faqItem->id;
        $this->question = $faqItem->question;
        $this->answer = $faqItem->answer;
        $this->display_order = $faqItem->display_order;
        $this->is_active = $faqItem->is_active;

        Flux::modal('faq-form')->show();
    }

    /**
     * Add or update the item.
     */
    public function save(): void
    {
        $validated = $this->validate([
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
            'display_order' => ['integer'],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingId) {
            FaqItem::findOrFail($this->editingId)->update($validated);
        } else {
            FaqItem::create($validated);
        }

        Flux::modal('faq-form')->close();
        Flux::toast(variant: 'success', heading: 'Success', text: 'FAQ item '.($this->editingId ? 'updated' : 'added').' successfully');

        $this->resetForm();
    }

    /**
     * Delete an item.
     */
    public function delete(int $faqItemId): void
    {
        FaqItem::findOrFail($faqItemId)->delete();

        Flux::toast(variant: 'success', heading: 'Success', text: 'FAQ item deleted successfully');
    }

    /**
     * Empty the form.
     */
    private function resetForm(): void
    {
        $this->reset('editingId', 'question', 'answer', 'display_order', 'is_active');
        $this->resetErrorBag();
    }
}; ?>

<x-ui.card>
    <x-ui.card.header>
        <div class="flex items-center justify-between">
            <x-ui.card.title>FAQ Management</x-ui.card.title>
            <flux:button wire:click="create" variant="primary" icon="plus">Add FAQ</flux:button>
        </div>
    </x-ui.card.header>

    <x-ui.card.content>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Question</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->items as $item)
                    <flux:table.row :key="$item->id">
                        <flux:table.cell variant="strong" class="max-w-md truncate">{{ $item->question }}</flux:table.cell>
                        <flux:table.cell>
                            <x-ui.badge :variant="$item->is_active ? 'default' : 'secondary'">{{ $item->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="edit({{ $item->id }})" variant="ghost" size="sm" icon="pencil-square" aria-label="Edit" />
                                <flux:button wire:click="delete({{ $item->id }})" wire:confirm="Are you sure you want to delete this FAQ item?" variant="ghost" size="sm" icon="trash" aria-label="Delete" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3" class="py-8 text-center text-muted-foreground">No FAQ items yet.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </x-ui.card.content>

    <flux:modal name="faq-form" class="w-full max-w-2xl">
        <form wire:submit="save" class="space-y-4">
            <flux:heading size="lg">{{ $editingId ? 'Edit FAQ' : 'Add FAQ Item' }}</flux:heading>

            <flux:input wire:model="question" label="Question *" required />
            <flux:textarea wire:model="answer" label="Answer *" rows="4" required />

            <div class="grid grid-cols-2 items-end gap-4">
                <flux:input wire:model="display_order" label="Display Order" type="number" />
                <div class="pb-2">
                    <flux:switch wire:model="is_active" label="Active" align="left" />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Update' : 'Add' }} FAQ</flux:button>
            </div>
        </form>
    </flux:modal>
</x-ui.card>
