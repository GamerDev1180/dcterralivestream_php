{{--
    Horizontal bars for magnitude per category. One hue for every bar: the colour
    is not encoding anything here, the length is.

    @param array<int, array{label: string, value: int|float, display?: string}> $rows
--}}
@props([
    'rows' => [],
    'color' => 'var(--viz-1)',
])

@php
    $max = max(1, ...array_column($rows, 'value') ?: [1]);
@endphp

<div {{ $attributes->class('space-y-3') }}>
    @forelse ($rows as $row)
        <div class="space-y-1">
            <div class="flex items-baseline justify-between gap-2 text-sm">
                <span class="min-w-0 truncate text-muted-foreground">{{ $row['label'] }}</span>
                <span class="shrink-0 font-medium text-foreground tabular-nums">{{ $row['display'] ?? $row['value'] }}</span>
            </div>

            <div class="h-2.5 w-full rounded-sm bg-muted">
                <div
                    class="h-full rounded-r-[4px]"
                    style="width: {{ $row['value'] > 0 ? max(1.5, round($row['value'] / $max * 100, 2)) : 0 }}%; background-color: {{ $color }}"
                ></div>
            </div>
        </div>
    @empty
        <p class="text-sm text-muted-foreground">Nog geen gegevens.</p>
    @endforelse
</div>
