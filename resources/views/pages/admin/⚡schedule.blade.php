<?php

use App\Enums\ScheduleEventType;
use App\Models\ScheduleEvent;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Programma')] class extends Component {
    /** The id of the event being edited, or null when adding a new one. */
    public ?int $editingId = null;

    public string $title = '';
    public string $description = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $event_type = 'special';
    public string $participants = '';
    public int $display_order = 0;
    public bool $is_active = true;

    /**
     * All events in chronological order.
     *
     * @return Collection<int, ScheduleEvent>
     */
    #[Computed]
    public function events(): Collection
    {
        return ScheduleEvent::orderBy('start_time')->orderBy('display_order')->get();
    }

    /**
     * Open an empty "Add Schedule Event" dialog.
     */
    public function create(): void
    {
        $this->resetForm();

        Flux::modal('schedule-event-form')->show();
    }

    /**
     * Open the dialog filled with an existing event.
     */
    public function edit(int $eventId): void
    {
        $event = ScheduleEvent::findOrFail($eventId);

        $this->resetForm();
        $this->editingId = $event->id;
        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->start_time = $event->start_time->format('Y-m-d\TH:i');
        $this->end_time = $event->end_time->format('Y-m-d\TH:i');
        $this->event_type = $event->event_type->value;
        $this->participants = implode(', ', $event->participants ?? []);
        $this->display_order = $event->display_order;
        $this->is_active = $event->is_active;

        Flux::modal('schedule-event-form')->show();
    }

    /**
     * Add or update the event.
     */
    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'event_type' => ['required', Rule::enum(ScheduleEventType::class)],
            'participants' => ['nullable', 'string'],
            'display_order' => ['integer'],
            'is_active' => ['boolean'],
        ]);

        $validated['description'] = $validated['description'] ?: null;
        $validated['participants'] = array_values(array_filter(array_map('trim', explode(',', $validated['participants'] ?? '')))) ?: null;

        if ($this->editingId) {
            ScheduleEvent::findOrFail($this->editingId)->update($validated);
        } else {
            ScheduleEvent::create($validated);
        }

        Flux::modal('schedule-event-form')->close();
        Flux::toast(variant: 'success', heading: 'Success', text: 'Event '.($this->editingId ? 'updated' : 'added').' successfully');

        $this->resetForm();
    }

    /**
     * Delete an event.
     */
    public function delete(int $eventId): void
    {
        ScheduleEvent::findOrFail($eventId)->delete();

        Flux::toast(variant: 'success', heading: 'Success', text: 'Event deleted successfully');
    }

    /**
     * Empty the form.
     */
    private function resetForm(): void
    {
        $this->reset('editingId', 'title', 'description', 'start_time', 'end_time', 'event_type', 'participants', 'display_order', 'is_active');
        $this->resetErrorBag();
    }
}; ?>

<x-ui.card>
    <x-ui.card.header>
        <div class="flex items-center justify-between">
            <x-ui.card.title>Schedule Management</x-ui.card.title>
            <flux:button wire:click="create" variant="primary" icon="plus">Add Event</flux:button>
        </div>
    </x-ui.card.header>

    <x-ui.card.content>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Title</flux:table.column>
                <flux:table.column>Start</flux:table.column>
                <flux:table.column>End</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->events as $event)
                    <flux:table.row :key="$event->id">
                        <flux:table.cell variant="strong">{{ $event->title }}</flux:table.cell>
                        <flux:table.cell>{{ $event->start_time->format('d-m-Y H:i') }}</flux:table.cell>
                        <flux:table.cell>{{ $event->end_time->format('d-m-Y H:i') }}</flux:table.cell>
                        <flux:table.cell><x-ui.badge variant="outline">{{ $event->event_type->value }}</x-ui.badge></flux:table.cell>
                        <flux:table.cell>
                            <x-ui.badge :variant="$event->is_active ? 'default' : 'secondary'">{{ $event->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="edit({{ $event->id }})" variant="ghost" size="sm" icon="pencil-square" aria-label="Edit" />
                                <flux:button wire:click="delete({{ $event->id }})" wire:confirm="Are you sure you want to delete this schedule event?" variant="ghost" size="sm" icon="trash" aria-label="Delete" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="py-8 text-center text-muted-foreground">No schedule events yet.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </x-ui.card.content>

    <flux:modal name="schedule-event-form" class="w-full max-w-2xl">
        <form wire:submit="save" class="space-y-4">
            <flux:heading size="lg">{{ $editingId ? 'Edit Event' : 'Add Schedule Event' }}</flux:heading>

            <flux:input wire:model="title" label="Title *" required />
            <flux:textarea wire:model="description" label="Description" rows="2" />

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="start_time" label="Start Time *" type="datetime-local" required />
                <flux:input wire:model="end_time" label="End Time *" type="datetime-local" required />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="event_type" label="Type">
                    @foreach (ScheduleEventType::cases() as $type)
                        <flux:select.option :value="$type->value">{{ ucfirst($type->value) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="display_order" label="Display Order" type="number" />
            </div>

            <flux:input wire:model="participants" label="Participants (comma-separated)" placeholder="Host, Guest Speaker" />

            @if ($editingId)
                <flux:switch wire:model="is_active" label="Active" align="left" />
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Update' : 'Add' }} Event</flux:button>
            </div>
        </form>
    </flux:modal>
</x-ui.card>
