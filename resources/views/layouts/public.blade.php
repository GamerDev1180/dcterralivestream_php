@php
    use App\Models\Setting;

    $orgName = Setting::getDisplayValue('org_name', 'DCTerra');
    $orgDescription = Setting::getDisplayValue('org_description', 'een goed doel dat we graag steunen.');
    $orgWebsite = Setting::getValue('org_website_url');
    $contactEmail = Setting::getValue('contact_email');

    // The menu only shows pages whose feature is switched on in "Site Settings".
    $navigationLinks = array_filter([
        ['label' => 'Home', 'footerLabel' => 'Home', 'route' => 'home', 'icon' => null, 'show' => true],
        ['label' => 'Registreren', 'footerLabel' => 'Registreren', 'route' => 'register', 'icon' => 'users', 'show' => Setting::isFeatureEnabled('registration')],
        ['label' => 'Schema', 'footerLabel' => 'Schema', 'route' => 'schedule', 'icon' => 'calendar', 'show' => Setting::isFeatureEnabled('schedule')],
        ['label' => 'Sponsors', 'footerLabel' => 'Sponsors', 'route' => 'sponsors', 'icon' => 'heart', 'show' => Setting::isFeatureEnabled('sponsors')],
        ['label' => 'Word vrijwilliger', 'footerLabel' => 'Team', 'route' => 'team', 'icon' => 'hand-raised', 'show' => Setting::isFeatureEnabled('team')],
    ], fn (array $link) => $link['show']);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-background font-sans text-foreground antialiased">
        {{-- Header --}}
        <header x-data="{ menuOpen: false }" class="sticky top-0 z-50 border-b border-primary/10 bg-background/80 shadow-[0_1px_0_0] shadow-primary/10 backdrop-blur-lg">
            <div class="container mx-auto px-4">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-8">
                        <a href="{{ route('home') }}" class="flex items-center gap-2" wire:navigate>
                            <flux:icon.heart class="size-6 text-primary drop-shadow-[0_0_8px_var(--primary)]" />
                            <span class="bg-gradient-to-r from-primary to-primary/80 bg-clip-text text-xl font-bold text-transparent">{{ $orgName }} Livestream</span>
                        </a>

                        {{-- Desktop navigation --}}
                        <nav class="hidden items-center gap-6 md:flex">
                            @foreach ($navigationLinks as $link)
                                <a href="{{ route($link['route']) }}" class="flex items-center gap-1 text-sm font-medium transition-colors hover:text-primary" wire:navigate>
                                    @if ($link['icon'])
                                        <flux:icon :name="$link['icon']" class="size-4" />
                                    @endif
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    <div class="flex items-center gap-4">
                        {{-- Theme toggle --}}
                        <x-ui.button variant="ghost" size="sm" x-data x-on:click="$flux.dark = ! $flux.dark">
                            <flux:icon.sun class="hidden size-4 dark:block" />
                            <flux:icon.moon class="size-4 dark:hidden" />
                            <span class="sr-only">Toggle theme</span>
                        </x-ui.button>

                        <x-ui.button variant="ghost" size="sm" :href="route('admin.registrations')" class="hidden sm:inline-flex">
                            <flux:icon.user-circle class="size-4" />
                            <span>Beheer</span>
                        </x-ui.button>

                        <x-ui.button variant="ghost" size="sm" class="md:hidden" x-on:click="menuOpen = true">
                            <flux:icon.bars-3 class="size-5" />
                            <span class="sr-only">Menu openen</span>
                        </x-ui.button>
                    </div>
                </div>
            </div>

            {{-- Mobile navigation (slides in from the right) --}}
            <div x-show="menuOpen" x-cloak class="fixed inset-0 z-50 md:hidden">
                <div x-show="menuOpen" x-transition.opacity class="absolute inset-0 bg-black/60" x-on:click="menuOpen = false"></div>

                <div
                    x-show="menuOpen"
                    x-transition:enter="transition duration-300 ease-out"
                    x-transition:enter-start="translate-x-full"
                    x-transition:leave="transition duration-200 ease-in"
                    x-transition:leave-end="translate-x-full"
                    class="absolute inset-y-0 right-0 flex w-[300px] flex-col gap-4 border-l bg-background p-6 sm:w-[400px]"
                >
                    <div class="mb-6 flex items-center gap-2">
                        <flux:icon.heart class="size-6 text-primary" />
                        <span class="text-lg font-bold">{{ $orgName }} Livestream</span>
                    </div>

                    @foreach ($navigationLinks as $link)
                        <a href="{{ route($link['route']) }}" class="flex items-center gap-3 py-2 text-lg font-medium transition-colors hover:text-primary" x-on:click="menuOpen = false" wire:navigate>
                            @if ($link['icon'])
                                <flux:icon :name="$link['icon']" class="size-5" />
                            @endif
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endforeach

                    <div class="mt-6 border-t pt-4">
                        <a href="{{ route('admin.registrations') }}" class="flex items-center gap-3 py-2 text-lg font-medium transition-colors hover:text-primary">
                            <flux:icon.user-circle class="size-5" />
                            <span>Beheer Inloggen</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        {{ $slot }}

        {{-- Footer --}}
        <footer class="mt-16 border-t border-border/40 bg-muted/30">
            <div class="container mx-auto px-4 py-12">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <flux:icon.heart class="size-6 text-primary" />
                            <span class="text-lg font-bold">{{ $orgName }} Livestream</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            24-uurs livestream evenement ter ondersteuning van {{ $orgName }}. {{ $orgDescription }}
                        </p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="font-semibold">Navigatie</h3>
                        <div class="space-y-2">
                            @foreach ($navigationLinks as $link)
                                <a href="{{ route($link['route']) }}" class="block text-sm text-muted-foreground transition-colors hover:text-primary" wire:navigate>{{ $link['footerLabel'] }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="font-semibold">Over</h3>
                        <div class="space-y-2">
                            @if ($orgWebsite)
                                <a href="{{ $orgWebsite }}" target="_blank" rel="noopener" class="flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-primary">
                                    <span>{{ $orgName }}</span>
                                    <flux:icon.arrow-top-right-on-square variant="micro" class="size-3" />
                                </a>
                            @endif
                            @if ($contactEmail)
                                <a href="mailto:{{ $contactEmail }}" class="block text-sm text-muted-foreground transition-colors hover:text-primary">{{ $contactEmail }}</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-border/40 pt-8 text-center">
                    <p class="text-sm text-muted-foreground">
                        &copy; {{ now()->year }} {{ $orgName }} Livestream Evenement. Gemaakt met
                        <flux:icon.heart variant="micro" class="inline size-4 text-primary" /> voor het goede doel.
                    </p>
                </div>
            </div>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
