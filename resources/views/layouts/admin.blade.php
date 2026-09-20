@php
    /**
     * Admin navigation, grouped so the top row stays short.
     * Every page is still its own route (see routes/web.php); the second row
     * only shows the pages of the group you are in.
     *
     * @var array<string, array{label: string, icon: string, pages: array<string, string>}> $navigation
     */
    $navigation = [
        'overzicht' => [
            'label' => 'Overzicht',
            'icon' => 'chart-bar',
            'pages' => [
                'admin.overview' => 'Grafieken',
            ],
        ],
        'aanmeldingen' => [
            'label' => 'Aanmeldingen',
            'icon' => 'clipboard-document-list',
            'pages' => [
                'admin.registrations' => 'Registraties',
                'admin.team-signups' => 'Team',
                'admin.questions' => 'Vragen',
                'admin.email-domains' => 'E-maildomeinen',
            ],
        ],
        'evenement' => [
            'label' => 'Evenement',
            'icon' => 'calendar-days',
            'pages' => [
                'admin.schedule' => 'Programma',
                'admin.event-timing' => 'Timing',
            ],
        ],
        'website' => [
            'label' => 'Website',
            'icon' => 'globe-alt',
            'pages' => [
                'admin.site-settings' => 'Site-instellingen',
                'admin.sponsors' => 'Sponsoren',
                'admin.faq' => 'FAQ',
            ],
        ],
        'beheer' => [
            'label' => 'Beheer',
            'icon' => 'cog-6-tooth',
            'pages' => [
                'admin.settings' => 'Systeem',
            ],
        ],
    ];

    $activeGroupKey = array_key_first(array_filter(
        $navigation,
        fn (array $group): bool => request()->routeIs(...array_keys($group['pages'])),
    )) ?? array_key_first($navigation);

    $activePages = $navigation[$activeGroupKey]['pages'];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-background font-sans text-foreground antialiased">
        {{-- Header --}}
        <header class="border-b border-border bg-card/50 backdrop-blur-sm">
            <div class="container mx-auto px-4">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-4">
                        <h1 class="text-xl font-bold text-primary">Admin Dashboard</h1>
                        <x-ui.badge variant="outline">24H Livestream Event</x-ui.badge>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-ui.button variant="ghost" size="sm" :href="route('home')" class="hidden sm:inline-flex">
                            <flux:icon.globe-alt class="size-4" />
                            Website
                        </x-ui.button>

                        <x-ui.button variant="ghost" size="sm" :href="route('profile.edit')" class="hidden sm:inline-flex">
                            <flux:icon.user-circle class="size-4" />
                            {{ auth()->user()->username ?? auth()->user()->name }}
                        </x-ui.button>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-ui.button type="submit" variant="ghost" size="sm" data-test="logout-button">
                                <flux:icon.arrow-right-start-on-rectangle class="size-4" />
                                Logout
                            </x-ui.button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="container mx-auto px-4 py-8">
            <livewire:admin.stats-overview />

            {{-- Groups --}}
            <nav aria-label="Onderdelen" class="grid grid-cols-2 gap-1 rounded-lg bg-muted p-[3px] text-muted-foreground sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($navigation as $groupKey => $group)
                    <a
                        href="{{ route(array_key_first($group['pages'])) }}"
                        wire:navigate
                        aria-current="{{ $groupKey === $activeGroupKey ? 'page' : 'false' }}"
                        @class([
                            'inline-flex items-center justify-center gap-2 rounded-md border px-3 py-1.5 text-sm font-medium whitespace-nowrap transition-[color,box-shadow]',
                            'border-input bg-background text-foreground shadow-sm dark:bg-input/30' => $groupKey === $activeGroupKey,
                            'border-transparent hover:text-foreground' => $groupKey !== $activeGroupKey,
                        ])
                    >
                        <flux:icon :name="$group['icon']" class="size-4" />
                        {{ $group['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Pages within the active group --}}
            @if (count($activePages) > 1)
                <nav aria-label="{{ $navigation[$activeGroupKey]['label'] }}" class="mt-3 flex flex-wrap items-center gap-1">
                    @foreach ($activePages as $routeName => $label)
                        <a
                            href="{{ route($routeName) }}"
                            wire:navigate
                            aria-current="{{ request()->routeIs($routeName) ? 'page' : 'false' }}"
                            @class([
                                'rounded-md px-3 py-1 text-sm transition-colors',
                                'bg-primary/10 font-medium text-primary' => request()->routeIs($routeName),
                                'text-muted-foreground hover:bg-muted hover:text-foreground' => ! request()->routeIs($routeName),
                            ])
                        >{{ $label }}</a>
                    @endforeach
                </nav>
            @endif

            <div class="mt-6">
                {{ $slot }}
            </div>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
