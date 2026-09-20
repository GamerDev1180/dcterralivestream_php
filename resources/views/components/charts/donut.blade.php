{{--
    Donut for a part-to-whole split. Every segment is direct-labelled beside the
    ring, so identity never rests on colour alone.

    @param array<int, array{name: string, color: string, value: int}> $segments
--}}
@props([
    'segments' => [],
    'total' => null,
    'totalLabel' => 'totaal',
])

@php
    $radius = 40;
    $circumference = 2 * M_PI * $radius;
    $sum = array_sum(array_column($segments, 'value'));
    $total ??= $sum;

    // A 1.5 unit gap in the surface colour keeps touching segments apart.
    $gap = count(array_filter($segments, fn (array $segment): bool => $segment['value'] > 0)) > 1 ? 1.5 : 0;
    $offset = 0;
    $arcs = [];

    foreach ($segments as $segment) {
        $share = $sum > 0 ? $segment['value'] / $sum : 0;
        $length = $share * $circumference;

        $arcs[] = [
            'color' => $segment['color'],
            'length' => max(0, $length - $gap),
            'offset' => -$offset,
            'visible' => $segment['value'] > 0,
        ];

        $offset += $length;
    }
@endphp

<div {{ $attributes->class('flex flex-wrap items-center gap-6') }}>
    <div class="relative size-32 shrink-0">
        <svg viewBox="0 0 100 100" class="size-full -rotate-90" aria-hidden="true">
            <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="var(--color-muted)" stroke-width="12" />

            @foreach ($arcs as $arc)
                @if ($arc['visible'])
                    <circle
                        cx="50"
                        cy="50"
                        r="{{ $radius }}"
                        fill="none"
                        stroke="{{ $arc['color'] }}"
                        stroke-width="12"
                        stroke-dasharray="{{ round($arc['length'], 3) }} {{ round($circumference, 3) }}"
                        stroke-dashoffset="{{ round($arc['offset'], 3) }}"
                    />
                @endif
            @endforeach
        </svg>

        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-2xl font-semibold text-foreground">{{ number_format($total, 0, ',', '.') }}</span>
            <span class="text-[11px] text-muted-foreground">{{ $totalLabel }}</span>
        </div>
    </div>

    <ul class="min-w-0 flex-1 space-y-2">
        @foreach ($segments as $segment)
            <li class="flex items-center gap-2 text-sm">
                <span class="size-2.5 shrink-0 rounded-full" style="background-color: {{ $segment['color'] }}"></span>
                <span class="min-w-0 truncate text-muted-foreground">{{ $segment['name'] }}</span>
                <span class="ml-auto shrink-0 font-medium text-foreground tabular-nums">{{ $segment['value'] }}</span>
                <span class="w-12 shrink-0 text-right text-xs text-muted-foreground tabular-nums">
                    {{ $sum > 0 ? round($segment['value'] / $sum * 100) : 0 }}%
                </span>
            </li>
        @endforeach
    </ul>
</div>
