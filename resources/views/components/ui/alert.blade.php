{{-- Alert, based on the shadcn/ui alert. Variants: default, destructive --}}
@props([
    'variant' => 'default',
])

<div role="alert" {{ $attributes->class([
    'flex w-full items-start gap-3 rounded-lg border bg-card px-4 py-3 text-sm',
    'text-destructive' => $variant === 'destructive',
    'text-card-foreground' => $variant !== 'destructive',
]) }}>
    <flux:icon.exclamation-circle class="mt-0.5 size-4 shrink-0" />
    <div @class(['grid gap-1', 'text-destructive/90' => $variant === 'destructive', 'text-muted-foreground' => $variant !== 'destructive'])>
        {{ $slot }}
    </div>
</div>
