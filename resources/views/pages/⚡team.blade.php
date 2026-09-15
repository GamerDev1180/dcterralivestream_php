<?php

use App\Concerns\TeamSignupValidationRules;
use App\Enums\TeamSignupStatus;
use App\Enums\TeamSignupType;
use App\Models\Setting;
use App\Models\TeamSignup;
use Illuminate\Support\Arr;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')] #[Title('Word vrijwilliger')] class extends Component {
    use TeamSignupValidationRules;

    public string $type = 'vrijwilliger';
    public string $name = '';
    public string $email = '';
    public string $discord_name = '';

    public bool $submitted = false;

    /**
     * Save the volunteer / organisation signup.
     */
    public function signUp(): void
    {
        abort_unless(Setting::isFeatureEnabled('team'), 404);

        // The form only asks for these fields (the API also accepts organisation name, phone and message).
        $rules = Arr::only($this->teamSignupRules(), ['type', 'name', 'email', 'discord_name']);

        $validated = $this->validate($rules, $this->teamSignupMessages());

        TeamSignup::create([
            ...$validated,
            'status' => TeamSignupStatus::Pending,
        ]);

        $this->submitted = true;
    }
}; ?>

<div>
    @if (! Setting::isFeatureEnabled('team'))
        <x-feature-unavailable title="Aanmelden is nog niet beschikbaar" text="Kom later terug om je aan te melden als vrijwilliger." />
    @else
        {{-- Hero --}}
        <section class="relative overflow-hidden border-b border-border/40">
            <div class="bg-mesh absolute inset-0"></div>
            <div class="relative container mx-auto px-4 pt-16 pb-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h1 class="mb-6 text-4xl font-bold text-balance md:text-5xl">
                        Word onderdeel van
                        <span class="text-glow block text-primary">het team</span>
                    </h1>
                    <p class="mx-auto max-w-2xl text-xl text-pretty text-muted-foreground">
                        Help ons dit evenement te laten slagen als vrijwilliger of sluit je aan als organisatie.
                    </p>
                </div>
            </div>
        </section>

        <main class="container mx-auto px-4 py-8">
            <div class="mx-auto max-w-2xl">
                @if ($submitted)
                    <x-ui.card class="glow-card">
                        <x-ui.card.content class="flex flex-col items-center space-y-4 py-12 text-center">
                            <div class="animate-glow-pulse rounded-full bg-primary/10 p-3">
                                <flux:icon.check-circle class="size-12 text-primary" />
                            </div>
                            <h2 class="text-2xl font-bold">Bedankt voor je aanmelding!</h2>
                            <p class="max-w-md text-muted-foreground">We hebben je aanmelding ontvangen en nemen zo snel mogelijk contact met je op.</p>
                        </x-ui.card.content>
                    </x-ui.card>
                @else
                    <x-ui.card>
                        <x-ui.card.header>
                            <div class="flex items-center gap-2">
                                <flux:icon.hand-raised class="size-5 text-primary" />
                                <x-ui.card.title>Word onderdeel van het team</x-ui.card.title>
                            </div>
                            <x-ui.card.description>
                                Meld je aan als vrijwilliger of als organisatie om ons te helpen dit evenement tot een succes te maken.
                            </x-ui.card.description>
                        </x-ui.card.header>

                        <x-ui.card.content>
                            <form wire:submit="signUp" class="space-y-6">
                                <div class="space-y-3">
                                    <flux:label>Ik meld me aan als *</flux:label>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        @foreach (TeamSignupType::cases() as $signupType)
                                            <label @class([
                                                'flex cursor-pointer items-center gap-2 rounded-md border p-3 hover:bg-muted',
                                                'border-primary' => $type === $signupType->value,
                                            ])>
                                                <input type="radio" wire:model.live="type" value="{{ $signupType->value }}" class="accent-primary">
                                                <span>{{ $signupType->label() }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <flux:input wire:model="name" label="Naam *" required autocomplete="name" />
                                <flux:input wire:model="email" label="E-mailadres *" type="email" required autocomplete="email" />
                                <flux:input wire:model="discord_name" label="Discord naam *" required />

                                <flux:button type="submit" variant="primary" class="w-full" data-test="team-signup-button">
                                    <span wire:loading.remove wire:target="signUp">Aanmelden</span>
                                    <span wire:loading wire:target="signUp">Versturen...</span>
                                </flux:button>
                            </form>
                        </x-ui.card.content>
                    </x-ui.card>
                @endif
            </div>
        </main>
    @endif
</div>
