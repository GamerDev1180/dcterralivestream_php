<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('admin.registrations') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Deelnemers" class="grid">
                    <flux:sidebar.item icon="users" :href="route('admin.registrations')" :current="request()->routeIs('admin.registrations')" wire:navigate>
                        Registraties
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="heart" :href="route('admin.team-signups')" :current="request()->routeIs('admin.team-signups')" wire:navigate>
                        Team aanmeldingen
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="envelope" :href="route('admin.email-domains')" :current="request()->routeIs('admin.email-domains')" wire:navigate>
                        E-mail domeinen
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.questions')" :current="request()->routeIs('admin.questions')" wire:navigate>
                        Vragen
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Evenement" class="grid">
                    <flux:sidebar.item icon="clock" :href="route('admin.event-timing')" :current="request()->routeIs('admin.event-timing')" wire:navigate>
                        Evenement timing
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar-days" :href="route('admin.schedule')" :current="request()->routeIs('admin.schedule')" wire:navigate>
                        Programma
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="star" :href="route('admin.sponsors')" :current="request()->routeIs('admin.sponsors')" wire:navigate>
                        Sponsors
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="question-mark-circle" :href="route('admin.faq')" :current="request()->routeIs('admin.faq')" wire:navigate>
                        FAQ
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group heading="Beheer" class="grid">
                    <flux:sidebar.item icon="adjustments-horizontal" :href="route('admin.site-settings')" :current="request()->routeIs('admin.site-settings')" wire:navigate>
                        Website instellingen
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.settings')" :current="request()->routeIs('admin.settings')" wire:navigate>
                        Admins & reset
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="globe-alt" :href="route('home')" target="_blank">
                    Bekijk website
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
