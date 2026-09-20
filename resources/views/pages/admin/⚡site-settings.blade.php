<?php

use App\Models\Setting;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Site-instellingen')] class extends Component {
    /**
     * Feature flags: turn public pages and nav links on or off.
     *
     * @var array<string, array{label: string, help: string}>
     */
    public const FEATURE_FLAGS = [
        'feature_registration_enabled' => ['label' => 'Registration', 'help' => 'Show the Register nav link and page'],
        'feature_schedule_enabled' => ['label' => 'Schedule', 'help' => 'Show the Schedule nav link and page'],
        'feature_sponsors_enabled' => ['label' => 'Sponsors', 'help' => 'Show the Sponsors nav link and page'],
        'feature_team_enabled' => ['label' => 'Team signup', 'help' => 'Show the Team nav link and volunteer/organisation form'],
        'feature_faq_enabled' => ['label' => 'FAQ', 'help' => 'Show the FAQ section on the home page'],
    ];

    /**
     * Organisation and content fields.
     *
     * @var array<string, array{label: string, placeholder?: string, multiline?: bool, rules: array<int, string>}>
     */
    public const ORG_FIELDS = [
        'org_name' => ['label' => 'Organisation name', 'rules' => ['string', 'max:255']],
        'org_website_url' => ['label' => 'Organisation website', 'placeholder' => 'https://example.org', 'rules' => ['nullable', 'url', 'max:255']],
        'org_description' => ['label' => 'Short description of the cause', 'multiline' => true, 'rules' => ['nullable', 'string', 'max:1000']],
        'event_venue' => ['label' => 'Event venue', 'rules' => ['nullable', 'string', 'max:255']],
        'discord_invite_link' => ['label' => 'Discord invite link', 'placeholder' => 'https://discord.gg/...', 'rules' => ['nullable', 'url', 'max:255']],
        'stream_platform_url' => ['label' => 'Live stream URL', 'rules' => ['nullable', 'url', 'max:255']],
        'contact_email' => ['label' => 'Public contact email', 'rules' => ['nullable', 'email', 'max:255']],
    ];

    /**
     * Manually entered stats (there is no payment integration).
     *
     * @var array<string, string>
     */
    public const STAT_FIELDS = [
        'stat_funds_raised_amount' => 'Funds raised (display value, e.g. "€2.450")',
        'stat_funds_goal_amount' => 'Fundraising goal (display value, e.g. "€10.000")',
        'stat_expected_participants' => 'Expected participants (display value, e.g. "100+")',
    ];

    /** @var array<string, bool> */
    public array $features = [];

    /** @var array<string, string> */
    public array $values = [];

    /**
     * Fill the form with the current settings.
     */
    public function mount(): void
    {
        foreach (array_keys(self::FEATURE_FLAGS) as $key) {
            $this->features[$key] = Setting::getBoolean($key);
        }

        foreach ([...array_keys(self::ORG_FIELDS), ...array_keys(self::STAT_FIELDS)] as $key) {
            $this->values[$key] = Setting::getValue($key);
        }
    }

    /**
     * Save a feature flag as soon as its switch is flipped.
     */
    public function updatedFeatures(bool $value, string $key): void
    {
        abort_unless(array_key_exists($key, self::FEATURE_FLAGS), 404);

        Setting::setValue($key, $value);
    }

    /**
     * Save a text field when it loses focus.
     */
    public function updatedValues(?string $value, string $key): void
    {
        abort_unless(array_key_exists($key, self::ORG_FIELDS) || array_key_exists($key, self::STAT_FIELDS), 404);

        $rules = self::ORG_FIELDS[$key]['rules'] ?? ['nullable', 'string', 'max:50'];

        $this->validateOnly("values.{$key}", ["values.{$key}" => $rules], [], ["values.{$key}" => 'value']);

        Setting::setValue($key, $value);
    }
}; ?>

<div class="space-y-6">
    {{-- Feature flags --}}
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title class="flex items-center gap-2">
                <flux:icon.flag class="size-5" />
                Feature Flags
            </x-ui.card.title>
            <x-ui.card.description>Turn public pages and nav links on or off for this year -- no code changes needed.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="space-y-4">
            @foreach ($this::FEATURE_FLAGS as $key => $flag)
                <div class="flex items-center justify-between rounded-lg bg-muted/50 p-3" wire:key="flag-{{ $key }}">
                    <div>
                        <p class="font-medium">{{ $flag['label'] }}</p>
                        <p class="text-sm text-muted-foreground">{{ $flag['help'] }}</p>
                    </div>
                    <flux:switch wire:model.live="features.{{ $key }}" :aria-label="$flag['label']" />
                </div>
            @endforeach
        </x-ui.card.content>
    </x-ui.card>

    {{-- Organisation & content --}}
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title class="flex items-center gap-2">
                <flux:icon.building-office-2 class="size-5" />
                Organisation & Content
            </x-ui.card.title>
            <x-ui.card.description>Shown across the public site -- update these each year instead of code.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="space-y-4">
            @foreach ($this::ORG_FIELDS as $key => $field)
                <div wire:key="field-{{ $key }}">
                    @if ($field['multiline'] ?? false)
                        <flux:textarea wire:model.blur="values.{{ $key }}" :label="$field['label']" :placeholder="$field['placeholder'] ?? null" rows="3" />
                    @else
                        <flux:input wire:model.blur="values.{{ $key }}" :label="$field['label']" :placeholder="$field['placeholder'] ?? null" />
                    @endif
                </div>
            @endforeach
        </x-ui.card.content>
    </x-ui.card>

    {{-- Stats --}}
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title class="flex items-center gap-2">
                <flux:icon.arrow-trending-up class="size-5" />
                Stats
            </x-ui.card.title>
            <x-ui.card.description>There is no payment integration, so funds raised is always entered manually here.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="space-y-4">
            @foreach ($this::STAT_FIELDS as $key => $label)
                <flux:input wire:model.blur="values.{{ $key }}" :label="$label" wire:key="stat-{{ $key }}" />
            @endforeach
        </x-ui.card.content>
    </x-ui.card>
</div>
