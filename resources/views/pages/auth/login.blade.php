<x-layouts::auth title="Admin Login">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-primary/10">
            <flux:icon.shield-check class="size-8 text-primary" />
        </div>
        <h1 class="text-2xl font-bold">Admin Login</h1>
        <p class="text-muted-foreground">24H Livestream Event Management</p>
    </div>

    <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Sign In</x-ui.card.title>
            <x-ui.card.description>Enter your admin credentials to access the dashboard</x-ui.card.description>
        </x-ui.card.header>

        <x-ui.card.content>
            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <flux:input
                    name="username"
                    label="Username"
                    :value="old('username')"
                    type="text"
                    placeholder="Enter your username"
                    required
                    autofocus
                    autocomplete="username"
                    error:class="hidden"
                />

                <flux:input
                    name="password"
                    label="Password"
                    type="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                    viewable
                    error:class="hidden"
                />

                @if ($errors->any())
                    <x-ui.alert variant="destructive">{{ $errors->first() }}</x-ui.alert>
                @endif

                <div class="flex items-center justify-between">
                    <flux:checkbox name="remember" label="Remember me" :checked="old('remember')" />

                    @if (Route::has('password.request'))
                        <flux:link class="text-sm" :href="route('password.request')" wire:navigate>Forgot password?</flux:link>
                    @endif
                </div>

                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">Sign In</flux:button>
            </form>
        </x-ui.card.content>
    </x-ui.card>

    <div class="mt-6 text-center text-sm text-muted-foreground">
        <p>DCTerra Event Management System</p>
    </div>
</x-layouts::auth>
