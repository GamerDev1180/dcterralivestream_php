{{--
    Card, based on the shadcn/ui card of the original site.
    Usage: <x-ui.card> <x-ui.card.header> <x-ui.card.title>..</x-ui.card.title> </x-ui.card.header> <x-ui.card.content>..</x-ui.card.content> </x-ui.card>
--}}
<div {{ $attributes->class('flex flex-col gap-6 rounded-xl border bg-card py-6 text-card-foreground shadow-sm') }}>
    {{ $slot }}
</div>
