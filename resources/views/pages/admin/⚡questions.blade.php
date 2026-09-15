<?php

use App\Enums\ParticipationType;
use App\Enums\QuestionType;
use App\Models\RegistrationQuestion;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Questions')] class extends Component {
    /** The id of the question being edited, or null when adding a new one. */
    public ?int $editingId = null;

    public string $question_text = '';
    public string $question_type = 'text';
    public string $category = 'on_camera';
    public string $options = '';
    public int $order_index = 0;
    public bool $is_required = true;
    public bool $is_active = true;

    public string $success = '';

    /**
     * All questions, ordered per category.
     *
     * @return Collection<int, RegistrationQuestion>
     */
    #[Computed]
    public function questions(): Collection
    {
        return RegistrationQuestion::orderBy('order_index')->orderBy('id')->get();
    }

    /**
     * Open an empty "Add New Question" dialog.
     */
    public function create(): void
    {
        $this->resetForm();

        Flux::modal('question-form')->show();
    }

    /**
     * Open the dialog filled with an existing question.
     */
    public function edit(int $questionId): void
    {
        $question = RegistrationQuestion::findOrFail($questionId);

        $this->resetForm();
        $this->editingId = $question->id;
        $this->question_text = $question->question_text;
        $this->question_type = $question->question_type->value;
        $this->category = $question->category->value;
        $this->options = implode(', ', $question->options ?? []);
        $this->order_index = $question->order_index;
        $this->is_required = $question->is_required;
        $this->is_active = $question->is_active;

        Flux::modal('question-form')->show();
    }

    /**
     * Add or update the question.
     */
    public function save(): void
    {
        $needsOptions = QuestionType::tryFrom($this->question_type)?->hasOptions() ?? false;

        $validated = $this->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', Rule::enum(QuestionType::class)],
            'category' => ['required', Rule::enum(ParticipationType::class)],
            'options' => [$needsOptions ? 'required' : 'nullable', 'string'],
            'order_index' => ['integer'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ], [
            'question_text.required' => 'Question text is required',
            'options.required' => 'Options are required for this question type',
        ]);

        $validated['options'] = $needsOptions
            ? array_values(array_filter(array_map('trim', explode(',', $validated['options']))))
            : null;

        if ($this->editingId) {
            RegistrationQuestion::findOrFail($this->editingId)->update($validated);
        } else {
            RegistrationQuestion::create($validated);
        }

        $this->success = $this->editingId ? 'Question updated successfully' : 'Question added successfully';

        Flux::modal('question-form')->close();

        $this->resetForm();
    }

    /**
     * Delete a question (and the answers that were given to it).
     */
    public function delete(int $questionId): void
    {
        RegistrationQuestion::findOrFail($questionId)->delete();

        $this->success = 'Question deleted successfully';
    }

    /**
     * Empty the form.
     */
    private function resetForm(): void
    {
        $this->reset('editingId', 'question_text', 'question_type', 'category', 'options', 'order_index', 'is_required', 'is_active');
        $this->resetErrorBag();
    }
}; ?>

<div class="space-y-6">
    @if ($success)
        <x-ui.alert>{{ $success }}</x-ui.alert>
    @endif

    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold">Registration Questions</h3>
            <p class="text-muted-foreground">Manage questions for on-camera and behind-the-scenes participants</p>
        </div>
        <flux:button wire:click="create" variant="primary" icon="plus">Add Question</flux:button>
    </div>

    @foreach ([
        ParticipationType::OnCamera->value => ['title' => 'On Camera Questions', 'description' => 'Questions for participants who want to be visible on camera', 'empty' => 'No on-camera questions configured.'],
        ParticipationType::OffCamera->value => ['title' => 'Behind the Scenes Questions', 'description' => 'Questions for participants who prefer to work behind the scenes', 'empty' => 'No behind-the-scenes questions configured.'],
    ] as $categoryValue => $section)
        @php($categoryQuestions = $this->questions->where('category', ParticipationType::from($categoryValue)))

        <x-ui.card wire:key="category-{{ $categoryValue }}">
            <x-ui.card.header>
                <x-ui.card.title class="flex items-center gap-2">
                    <flux:icon :name="ParticipationType::from($categoryValue)->icon()" class="size-5" />
                    <span>{{ $section['title'] }}</span>
                </x-ui.card.title>
                <x-ui.card.description>{{ $section['description'] }}</x-ui.card.description>
            </x-ui.card.header>

            <x-ui.card.content>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Order</flux:table.column>
                        <flux:table.column>Question</flux:table.column>
                        <flux:table.column>Type</flux:table.column>
                        <flux:table.column>Required</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($categoryQuestions as $question)
                            <flux:table.row :key="$question->id">
                                <flux:table.cell>{{ $question->order_index }}</flux:table.cell>
                                <flux:table.cell class="max-w-xs truncate">{{ $question->question_text }}</flux:table.cell>
                                <flux:table.cell><x-ui.badge variant="outline">{{ $question->question_type->value }}</x-ui.badge></flux:table.cell>
                                <flux:table.cell>
                                    <x-ui.badge :variant="$question->is_required ? 'default' : 'secondary'">{{ $question->is_required ? 'Required' : 'Optional' }}</x-ui.badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <x-ui.badge :variant="$question->is_active ? 'default' : 'secondary'">{{ $question->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button wire:click="edit({{ $question->id }})" variant="ghost" size="sm" icon="pencil-square" aria-label="Edit" />
                                        <flux:button wire:click="delete({{ $question->id }})" wire:confirm="Are you sure you want to delete this question?" variant="ghost" size="sm" aria-label="Delete">
                                            <flux:icon.trash class="size-4 text-red-600" />
                                        </flux:button>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                @if ($categoryQuestions->isEmpty())
                    <div class="py-8 text-center text-muted-foreground">{{ $section['empty'] }}</div>
                @endif
            </x-ui.card.content>
        </x-ui.card>
    @endforeach

    <flux:modal name="question-form" class="w-full max-w-2xl">
        <form wire:submit="save" class="space-y-4">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Edit Question' : 'Add New Question' }}</flux:heading>
                <flux:text>Create or modify registration questions for participants</flux:text>
            </div>

            <flux:textarea wire:model="question_text" label="Question Text *" placeholder="Enter your question..." rows="3" />

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model.live="question_type" label="Question Type">
                    @foreach (QuestionType::cases() as $type)
                        <flux:select.option :value="$type->value">{{ $type->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="category" label="Category">
                    @foreach (ParticipationType::cases() as $participationType)
                        <flux:select.option :value="$participationType->value">{{ $participationType->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            @if (QuestionType::tryFrom($question_type)?->hasOptions())
                <flux:textarea wire:model="options" label="Options (comma-separated)" placeholder="Option 1, Option 2, Option 3" rows="2" />
            @endif

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="order_index" label="Order Index" type="number" />
                <div class="space-y-2">
                    <flux:switch wire:model="is_required" label="Required" align="left" />
                    <flux:switch wire:model="is_active" label="Active" align="left" />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Update Question' : 'Add Question' }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
