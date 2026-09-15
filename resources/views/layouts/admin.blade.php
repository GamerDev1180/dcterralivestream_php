@php
    // Every tab is a separate page (see routes/web.php).
    $tabs = [
        'admin.registrations' => 'Registrations',
        'admin.team-signups' => 'Team Aanmeldingen',
        'admin.schedule' => 'Schedule',
        'admin.event-timing' => 'Event Timing',
        'admin.site-settings' => 'Site Settings',
        'admin.sponsors' => 'Sponsors',
        'admin.faq' => 'FAQ',
        'admin.email-domains' => 'Email Domains',
        'admin.questions' => 'Questions',
        'admin.settings' => 'Settings',
    ];
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

            {{-- Tabs --}}
            <nav class="mb-6 grid h-auto w-full grid-cols-2 gap-1 rounded-lg bg-muted p-[3px] text-muted-foreground sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($tabs as $routeName => $label)
                    <a
                        href="{{ route($routeName) }}"
                        wire:navigate
                        @class([
                            'inline-flex items-center justify-center rounded-md border px-2 py-1 text-sm font-medium whitespace-nowrap transition-[color,box-shadow]',
                            'border-input bg-background text-foreground shadow-sm dark:bg-input/30' => request()->routeIs($routeName),
                            'border-transparent hover:text-foreground' => ! request()->routeIs($routeName),
                        ])
                    >{{ $label }}</a>
                @endforeach
            </nav>

            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
