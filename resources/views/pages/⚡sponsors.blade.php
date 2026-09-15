<?php

use App\Enums\SponsorTier;
use App\Models\Setting;
use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')] #[Title('Sponsors')] class extends Component {
    /**
     * All active sponsors.
     *
     * @return Collection<int, Sponsor>
     */
    #[Computed]
    public function sponsors(): Collection
    {
        return Sponsor::activeInOrder()->get();
    }
}; ?>

@php
    $orgName = Setting::getDisplayValue('org_name', 'DCTerra');
    $contactEmail = Setting::getDisplayValue('contact_email', 'contact@example.org');

    // Each tier gets its own card size and grid, bigger sponsors get bigger cards.
    $tierLayouts = [
        SponsorTier::Platinum->value => ['grid' => 'md:grid-cols-2 gap-8', 'padding' => 'p-8', 'logo' => 'h-12', 'title' => 'text-xl'],
        SponsorTier::Gold->value => ['grid' => 'md:grid-cols-2 lg:grid-cols-3 gap-6', 'padding' => 'p-6', 'logo' => 'h-10', 'title' => 'text-lg'],
        SponsorTier::Silver->value => ['grid' => 'md:grid-cols-2 lg:grid-cols-4 gap-4', 'padding' => 'p-4', 'logo' => 'h-8', 'title' => 'text-base'],
        SponsorTier::Bronze->value => ['grid' => 'grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4', 'padding' => 'p-3 text-center', 'logo' => 'h-6 mx-auto', 'title' => 'text-sm'],
    ];
@endphp

<div>
    @if (! Setting::isFeatureEnabled('sponsors'))
        <x-feature-unavailable title="Sponsors pagina is nog niet beschikbaar" text="Kom later terug om onze sponsors te bekijken." />
    @else
        {{-- Hero --}}
        <section class="relative overflow-hidden border-b border-border/40">
            <div class="bg-mesh absolute inset-0"></div>
            <div class="relative container mx-auto px-4 py-16">
                <div class="mx-auto max-w-4xl text-center">
                    <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-medium text-primary">
                        <flux:icon.heart class="size-4" />
                        <span>Met dank aan onze sponsors</span>
                    </div>

                    <h1 class="mb-6 text-4xl font-bold text-balance md:text-6xl">
                        Onze geweldige
                        <span class="text-glow block text-primary">sponsors</span>
                    </h1>

                    <p class="mx-auto mb-8 max-w-2xl text-xl text-pretty text-muted-foreground">
                        Deze fantastische organisaties maken onze 24U livestream mogelijk. Hun steun helpt ons om geld in te zamelen voor {{ $orgName }} en een geweldig evenement neer te zetten.
                    </p>
                </div>
            </div>
        </section>

        {{-- Sponsors per tier --}}
        <section class="py-16">
            <div class="container mx-auto px-4">
                <div class="mx-auto max-w-6xl">
                    @foreach (SponsorTier::cases() as $tier)
                        @php
                            $tierSponsors = $this->sponsors->where('tier', $tier);
                            $layout = $tierLayouts[$tier->value];
                        @endphp

                        @if ($tierSponsors->isNotEmpty())
                            <div class="mb-16">
                                <h2 class="mb-8 flex items-center justify-center gap-2 text-center text-2xl font-bold">
                                    <div class="size-6 rounded {{ $tier->gradientClasses() }}"></div>
                                    <span>{{ $tier->sectionTitle() }}</span>
                                </h2>

                                <div class="grid grid-cols-1 {{ $layout['grid'] }}">
                                    @foreach ($tierSponsors->values() as $index => $sponsor)
                                        <x-reveal :delay="min($index * 0.08, 0.5)" wire:key="sponsor-{{ $sponsor->id }}">
                                            <x-ui.card class="glow-card h-full py-0">
                                                <div class="{{ $layout['padding'] }}">
                                                    @if ($tier === SponsorTier::Bronze)
                                                        @if ($sponsor->logo_url)
                                                            <img src="{{ $sponsor->logo_url }}" alt="{{ $sponsor->name }} logo" class="mb-2 object-contain {{ $layout['logo'] }}">
                                                        @endif
                                                    @else
                                                        <div class="mb-4 flex items-center justify-between gap-2">
                                                            @if ($sponsor->logo_url)
                                                                <img src="{{ $sponsor->logo_url }}" alt="{{ $sponsor->name }} logo" class="object-contain {{ $layout['logo'] }}">
                                                            @else
                                                                <span></span>
                                                            @endif
                                                            <x-ui.badge class="text-white {{ $tier->gradientClasses() }}">{{ $tier->label() }}</x-ui.badge>
                                                        </div>
                                                    @endif

                                                    <h3 class="mb-2 font-semibold {{ $layout['title'] }}">{{ $sponsor->name }}</h3>

                                                    @if ($sponsor->description && in_array($tier, [SponsorTier::Platinum, SponsorTier::Gold]))
                                                        <p class="mb-4 text-muted-foreground {{ $tier === SponsorTier::Gold ? 'text-sm' : '' }}">{{ $sponsor->description }}</p>
                                                    @endif

                                                    @if ($sponsor->contribution && $tier !== SponsorTier::Bronze)
                                                        <div class="mb-4 text-sm">
                                                            @if ($tier === SponsorTier::Silver)
                                                                <p class="text-xs text-muted-foreground">{{ $sponsor->contribution }}</p>
                                                            @else
                                                                <span class="font-medium text-primary">Bijdrage: </span>
                                                                <span class="text-muted-foreground">{{ $sponsor->contribution }}</span>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if ($sponsor->website)
                                                        <x-ui.button variant="outline" size="sm" :href="$sponsor->website" target="_blank" rel="noopener">
                                                            <flux:icon.arrow-top-right-on-square variant="micro" class="size-4" />
                                                            @if ($tier === SponsorTier::Platinum)
                                                                <span>Bezoek Website</span>
                                                            @elseif ($tier !== SponsorTier::Bronze)
                                                                <span>Bezoek</span>
                                                            @endif
                                                        </x-ui.button>
                                                    @endif
                                                </div>
                                            </x-ui.card>
                                        </x-reveal>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if ($this->sponsors->isEmpty())
                        <p class="text-center text-muted-foreground">Sponsors worden binnenkort bekendgemaakt.</p>
                    @endif
                </div>
            </div>
        </section>

        {{-- Become a sponsor --}}
        <section class="relative overflow-hidden border-t border-border/40 py-16">
            <div class="bg-mesh absolute inset-0 opacity-70"></div>
            <div class="relative container mx-auto px-4 text-center">
                <div class="mx-auto max-w-2xl">
                    <h2 class="mb-4 text-3xl font-bold md:text-4xl">Word Sponsor</h2>
                    <p class="mb-8 text-xl text-muted-foreground">Sluit je aan bij deze geweldige organisaties en steun ons doel terwijl je onze gemeenschap bereikt</p>
                    <x-ui.button size="lg" href="mailto:{{ $contactEmail }}" class="px-8 text-lg shadow-lg shadow-primary/30">
                        <flux:icon.envelope class="size-5" />
                        <span>Neem Contact Op</span>
                    </x-ui.button>
                </div>
            </div>
        </section>
    @endif
</div>
