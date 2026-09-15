<?php

use App\Actions\RegisterParticipant;
use App\Concerns\RegistrationValidationRules;
use App\Enums\QuestionType;
use App\Models\RegistrationQuestion;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')] #[Title('Registreren')] class extends Component {
    use RegistrationValidationRules;

    /**
     * The steps of the registration wizard.
     *
     * @var array<int, array{title: string, description: string, icon: string}>
     */
    public const STEPS = [
        1 => ['title' => 'Gegevens', 'description' => 'Naam en e-mail', 'icon' => 'user'],
        2 => ['title' => 'Vragen', 'description' => 'Aanvullende informatie', 'icon' => 'question-mark-circle'],
        3 => ['title' => 'Bevestiging', 'description' => 'Controleren en versturen', 'icon' => 'check'],
    ];

    public int $step = 1;

    public string $name = '';
    public string $email = '';

    /** @var array<int, string|array<int, string>> Answers keyed by question id */
    public array $answers = [];

    public bool $submitted = false;

    /**
     * Determine if people can currently register.
     */
    #[Computed]
    public function registrationIsOpen(): bool
    {
        return Setting::isRegistrationOpen();
    }

    /**
     * The questions asked in step 2.
     *
     * @return Collection<int, RegistrationQuestion>
     */
    #[Computed]
    public function questions(): Collection
    {
        return $this->registrationQuestions();
    }

    #[Computed]
    public function streamStartTime(): ?CarbonImmutable
    {
        return Setting::getDate('stream_start_time');
    }

    #[Computed]
    public function registrationEndTime(): ?CarbonImmutable
    {
        return Setting::getDate('registration_end_time');
    }

    /**
     * Step 1 -> 2: check the name and email address.
     */
    public function goToQuestions(): void
    {
        $this->validate($this->personalInfoRules(), $this->registrationMessages());

        $this->step = 2;
    }

    /**
     * Step 2 -> 3: check the answers.
     */
    public function goToConfirmation(): void
    {
        $this->validate(
            $this->answerRules($this->questions),
            $this->registrationMessages(),
            $this->answerAttributes($this->questions),
        );

        $this->step = 3;
    }

    /**
     * Go back to the previous step.
     */
    public function previousStep(): void
    {
        $this->resetErrorBag();
        $this->step = max(1, $this->step - 1);
    }

    /**
     * Step 3: save the registration.
     */
    public function submit(RegisterParticipant $registerParticipant): void
    {
        if (! $this->registrationIsOpen) {
            $this->addError('registration', 'De registratie is gesloten.');

            return;
        }

        $this->validate(
            [...$this->personalInfoRules(), ...$this->answerRules($this->questions)],
            $this->registrationMessages(),
            $this->answerAttributes($this->questions),
        );

        $registerParticipant->handle($this->name, $this->email, $this->answers);

        $this->submitted = true;
    }
}; ?>

<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-border/40">
        <div class="bg-mesh absolute inset-0"></div>
        <div class="relative container mx-auto px-4 pt-16 pb-8">
            <div class="mx-auto max-w-4xl text-center">
                <h1 class="mb-6 text-4xl font-bold text-balance md:text-5xl">
                    Registreer voor de
                    <span class="text-glow block text-primary">24U Livestream</span>
                </h1>
                <p class="mx-auto max-w-2xl text-xl text-pretty text-muted-foreground">
                    Voltooi je registratie om deel te nemen aan ons 24-uurs livestream evenement ter ondersteuning van {{ Setting::getDisplayValue('org_name', 'DCTerra') }}.
                </p>
            </div>
        </div>
    </section>

    <main class="container mx-auto px-4 py-8">
        <div class="mx-auto max-w-4xl">
            @if ($this->streamStartTime && $this->registrationEndTime)
                <div class="mb-8">
                    <x-event-timer
                        :stream-start-time="$this->streamStartTime"
                        :registration-end-time="$this->registrationEndTime"
                        :registration-open="Setting::getBoolean('registration_open')"
                    />
                </div>
            @endif

            @if (! $this->registrationIsOpen && ! $submitted)
                <x-registration-closed :stream-start-time="$this->streamStartTime" :registration-end-time="$this->registrationEndTime" />
            @else
                <div class="mx-auto max-w-3xl">
                    {{-- Progress header --}}
                    <div class="mb-8">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-2xl font-semibold">Registratie</h2>
                            <span class="text-sm text-muted-foreground">Stap {{ $step }} van {{ count($this::STEPS) }}</span>
                        </div>

                        <div class="relative mb-6 h-2 w-full overflow-hidden rounded-full bg-primary/20">
                            <div class="h-full bg-primary transition-all" style="width: {{ $step / count($this::STEPS) * 100 }}%"></div>
                        </div>

                        <div class="flex items-center justify-between">
                            @foreach ($this::STEPS as $number => $stepInfo)
                                @php
                                    $isActive = $step === $number && ! $submitted;
                                    $isCompleted = $step > $number || $submitted;
                                @endphp
                                <div class="flex flex-col items-center gap-2">
                                    <div @class([
                                        'relative flex size-8 items-center justify-center rounded-full border-2 text-sm font-medium transition-all',
                                        'border-primary bg-primary text-primary-foreground' => $isActive,
                                        'border-success bg-success text-success-foreground' => $isCompleted,
                                        'border-muted-foreground bg-background text-muted-foreground' => ! $isActive && ! $isCompleted,
                                    ])>
                                        <flux:icon :name="$isCompleted ? 'check-circle' : $stepInfo['icon']" class="size-4" />
                                    </div>
                                    <div class="text-center">
                                        <p @class([
                                            'text-xs font-medium',
                                            'text-primary' => $isActive,
                                            'text-success' => $isCompleted,
                                            'text-muted-foreground' => ! $isActive && ! $isCompleted,
                                        ])>{{ $stepInfo['title'] }}</p>
                                        <p class="hidden text-xs text-muted-foreground sm:block">{{ $stepInfo['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Step content --}}
                    <x-ui.card class="animate-fade-in glow-card" wire:key="step-{{ $step }}-{{ $submitted ? 'done' : 'form' }}">
                        <x-ui.card.header>
                            <x-ui.card.title class="flex items-center gap-2">
                                <flux:icon :name="$this::STEPS[$step]['icon']" class="size-5" />
                                <span>{{ $this::STEPS[$step]['title'] }}</span>
                            </x-ui.card.title>
                            <x-ui.card.description>{{ $this::STEPS[$step]['description'] }}</x-ui.card.description>
                        </x-ui.card.header>

                        <x-ui.card.content>
                            @if ($submitted)
                                {{-- Success --}}
                                <div class="space-y-6 text-center" data-test="registration-success">
                                    <div class="animate-glow-pulse mx-auto flex size-16 items-center justify-center rounded-full bg-primary/10">
                                        <flux:icon.check-circle class="size-8 text-primary" />
                                    </div>

                                    <div>
                                        <h3 class="text-glow mb-2 text-2xl font-bold text-primary">Registratie Gelukt!</h3>
                                        <p class="text-muted-foreground">Bedankt voor je registratie voor het 24U Livestream evenement.</p>
                                    </div>

                                    <x-ui.card class="glow-card">
                                        <x-ui.card.header>
                                            <x-ui.card.title class="flex items-center gap-2">
                                                <flux:icon.envelope class="size-5" />
                                                <span>Controleer je E-mail</span>
                                            </x-ui.card.title>
                                        </x-ui.card.header>
                                        <x-ui.card.content>
                                            <p class="mb-4 text-sm text-muted-foreground">We hebben je een e-mail gestuurd met:</p>
                                            <ul class="space-y-1 text-left text-sm">
                                                <li>• Evenementregels en richtlijnen</li>
                                                <li>• Discord server uitnodigingslink</li>
                                                <li>• Jouw unieke Discord toegangscode</li>
                                                <li>• Programma en evenementdetails</li>
                                            </ul>
                                        </x-ui.card.content>
                                    </x-ui.card>

                                    <p class="text-xs text-muted-foreground">Als je de e-mail niet binnen een paar minuten ontvangt, controleer dan je spamfolder.</p>
                                </div>
                            @elseif ($step === 1)
                                {{-- Step 1: personal information --}}
                                <form wire:submit="goToQuestions" class="space-y-6">
                                    <div class="space-y-4">
                                        <flux:input wire:model="name" label="Volledige Naam *" placeholder="Vul je volledige naam in" autocomplete="name" error:class="hidden" />
                                        <flux:input
                                            wire:model="email"
                                            type="email"
                                            label="E-mailadres *"
                                            placeholder="jouw.naam@dcterra.nl of 123456@student.dcterra.nl"
                                            description:trailing="Alleen toegestane school e-mailadressen (zoals @dcterra.nl en @student.dcterra.nl) kunnen zich registreren"
                                            autocomplete="email"
                                            error:class="hidden"
                                        />
                                    </div>

                                    @if ($errors->any())
                                        <x-ui.alert variant="destructive">{{ $errors->first() }}</x-ui.alert>
                                    @endif

                                    <div class="flex justify-end">
                                        <flux:button type="submit" variant="primary">Doorgaan</flux:button>
                                    </div>
                                </form>
                            @elseif ($step === 2)
                                {{-- Step 2: questions --}}
                                <form wire:submit="goToConfirmation" class="space-y-6">
                                    <div class="mb-6 text-center">
                                        <h3 class="mb-2 text-lg font-semibold">Vragen</h3>
                                        <p class="text-muted-foreground">Beantwoord de volgende vragen zodat we het evenement beter kunnen organiseren.</p>
                                    </div>

                                    <div class="space-y-6">
                                        @foreach ($this->questions as $question)
                                            @php($label = $question->question_text.($question->is_required ? ' *' : ''))

                                            <div wire:key="question-{{ $question->id }}">
                                                @switch($question->question_type)
                                                    @case(QuestionType::Text)
                                                        <flux:input wire:model="answers.{{ $question->id }}" :label="$label" placeholder="Vul je antwoord in" error:class="hidden" />
                                                        @break

                                                    @case(QuestionType::Textarea)
                                                        <flux:textarea wire:model="answers.{{ $question->id }}" :label="$label" placeholder="Vul je antwoord in" rows="3" error:class="hidden" />
                                                        @break

                                                    @case(QuestionType::Select)
                                                        <flux:select wire:model="answers.{{ $question->id }}" :label="$label" placeholder="Kies een optie" error:class="hidden">
                                                            @foreach ($question->options ?? [] as $option)
                                                                <flux:select.option :value="$option">{{ $option }}</flux:select.option>
                                                            @endforeach
                                                        </flux:select>
                                                        @break

                                                    @case(QuestionType::Radio)
                                                        <flux:radio.group wire:model="answers.{{ $question->id }}" :label="$label" error:class="hidden">
                                                            @foreach ($question->options ?? [] as $option)
                                                                <flux:radio :value="$option" :label="$option" />
                                                            @endforeach
                                                        </flux:radio.group>
                                                        @break

                                                    @case(QuestionType::Checkbox)
                                                        <flux:checkbox.group wire:model="answers.{{ $question->id }}" :label="$label" error:class="hidden">
                                                            @foreach ($question->options ?? [] as $option)
                                                                <flux:checkbox :value="$option" :label="$option" />
                                                            @endforeach
                                                        </flux:checkbox.group>
                                                        @break
                                                @endswitch
                                            </div>
                                        @endforeach
                                    </div>

                                    @if ($errors->any())
                                        <x-ui.alert variant="destructive">{{ $errors->first() }}</x-ui.alert>
                                    @endif

                                    <div class="flex justify-between">
                                        <flux:button type="button" wire:click="previousStep">Terug</flux:button>
                                        <flux:button type="submit" variant="primary">Doorgaan</flux:button>
                                    </div>
                                </form>
                            @else
                                {{-- Step 3: confirmation --}}
                                <div class="space-y-6">
                                    <div class="mb-6 text-center">
                                        <h3 class="mb-2 text-lg font-semibold">Controleer je Registratie</h3>
                                        <p class="text-muted-foreground">Controleer je gegevens voordat je je registratie verstuurt.</p>
                                    </div>

                                    <x-ui.card>
                                        <x-ui.card.header>
                                            <x-ui.card.title>Persoonlijke Gegevens</x-ui.card.title>
                                        </x-ui.card.header>
                                        <x-ui.card.content class="space-y-3">
                                            <div class="flex justify-between gap-4">
                                                <span class="font-medium">Naam:</span>
                                                <span>{{ $name }}</span>
                                            </div>
                                            <div class="flex justify-between gap-4">
                                                <span class="font-medium">E-mail:</span>
                                                <span>{{ $email }}</span>
                                            </div>
                                        </x-ui.card.content>
                                    </x-ui.card>

                                    @if ($this->questions->isNotEmpty())
                                        <x-ui.card>
                                            <x-ui.card.header>
                                                <x-ui.card.title>Jouw Antwoorden</x-ui.card.title>
                                                <x-ui.card.description>Jouw antwoorden op de registratievragen</x-ui.card.description>
                                            </x-ui.card.header>
                                            <x-ui.card.content>
                                                <div class="space-y-3 text-sm">
                                                    @foreach ($this->questions as $question)
                                                        @php($answer = $answers[$question->id] ?? null)
                                                        <div>
                                                            <p class="font-medium text-muted-foreground">{{ $question->question_text }}</p>
                                                            <p>{{ (is_array($answer) ? implode(', ', $answer) : $answer) ?: '-' }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </x-ui.card.content>
                                        </x-ui.card>
                                    @endif

                                    @if ($errors->any())
                                        <x-ui.alert variant="destructive">{{ $errors->first() }}</x-ui.alert>
                                    @endif

                                    <div class="flex justify-between">
                                        <flux:button type="button" wire:click="previousStep" wire:loading.attr="disabled" wire:target="submit">Terug</flux:button>
                                        <flux:button wire:click="submit" variant="primary" data-test="register-button">Registratie Versturen</flux:button>
                                    </div>
                                </div>
                            @endif
                        </x-ui.card.content>
                    </x-ui.card>
                </div>
            @endif
        </div>
    </main>
</div>
