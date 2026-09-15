{{-- Badge, based on the shadcn/ui badge. Variants: default, secondary, destructive, outline --}}
@props([
    'variant' => 'default',
    'icon' => null,
])

<span {{ $attributes->class([
    'inline-flex w-fit shrink-0 items-center justify-center gap-1 overflow-hidden rounded-md border px-2 py-0.5 text-xs font-medium whitespace-nowrap',
    match ($variant) {
        'secondary' => 'border-transparent bg-secondary text-secondary-foreground',
        'destructive' => 'border-transparent bg-destructive text-white dark:bg-destructive/60',
        'outline' => 'text-foreground',
        default => 'border-transparent bg-primary text-primary-foreground',
    },
]) }}>
    @if ($icon)
        <flux:icon :name="$icon" variant="micro" class="size-3" />
    @endif
    {{ $slot }}
</span>
