{{--
    Button, based on the shadcn/ui button. Renders a link when "href" is given.
    Variants: default, destructive, outline, secondary, ghost. Sizes: default, sm, lg.
--}}
@props([
    'variant' => 'default',
    'size' => 'default',
    'href' => null,
])

@php
    $classes = [
        'inline-flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-md text-sm font-medium whitespace-nowrap transition-all outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50',
        match ($variant) {
            'destructive' => 'bg-destructive text-white shadow-xs hover:bg-destructive/90 dark:bg-destructive/60',
            'outline' => 'border bg-background shadow-xs hover:bg-muted dark:border-input dark:bg-input/30 dark:hover:bg-input/50',
            'secondary' => 'bg-secondary text-secondary-foreground shadow-xs hover:bg-secondary/80',
            'ghost' => 'hover:bg-muted',
            default => 'bg-primary text-primary-foreground shadow-xs hover:bg-primary/90',
        },
        match ($size) {
            'sm' => 'h-8 gap-1.5 px-3',
            'lg' => 'h-10 px-6',
            default => 'h-9 px-4 py-2',
        },
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button'])->class($classes) }}>{{ $slot }}</button>
@endif
