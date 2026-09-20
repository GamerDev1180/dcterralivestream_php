{{--
    Single-series area + line, for a running total. The <svg> is stretched to the
    container and only holds the path; gridlines, labels and the end marker are
    ordinary elements, so nothing is distorted by the non-uniform scale.

    @param array<int, array{short: string, full: string}> $labels
    @param array<int, int> $values
--}}
@props([
    'labels' => [],
    'values' => [],
    'color' => 'var(--viz-1)',
    'height' => 180,
])

@php
    $pointCount = count($values);
    $max = max(1, ...$values ?: [1]);
    $lastIndex = max(0, $pointCount - 1);

    $coordinates = [];

    foreach (array_values($values) as $index => $value) {
        $coordinates[] = [
            'x' => $pointCount > 1 ? round($index / $lastIndex * 100, 3) : 0,
            'y' => round(100 - ($value / $max * 100), 3),
        ];
    }

    $line = collect($coordinates)->map(fn (array $point): string => "{$point['x']},{$point['y']}")->implode(' ');
    $area = $line !== '' ? "0,100 {$line} 100,100" : '';

    $tickStep = max(1, (int) ceil($pointCount / 8));
@endphp

<div {{ $attributes->class('space-y-3') }}>
    <div class="grid gap-x-3 gap-y-1.5" style="grid-template-columns: auto minmax(0, 1fr)">
        {{-- Y axis --}}
        <div class="flex shrink-0 flex-col justify-between text-[11px] text-muted-foreground tabular-nums" style="height: {{ $height }}px">
            <span>{{ number_format($max, 0, ',', '.') }}</span>
            <span>0</span>
        </div>

        <div class="relative min-w-0" style="height: {{ $height }}px">
            {{-- Recessive gridlines --}}
            @foreach ([0, 25, 50, 75, 100] as $gridline)
                <div class="absolute inset-x-0 border-t border-border" style="top: {{ $gridline }}%"></div>
            @endforeach

            <svg class="absolute inset-0 size-full" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                @if ($area !== '')
                    <polygon points="{{ $area }}" fill="{{ $color }}" fill-opacity="0.1" />
                    <polyline
                        points="{{ $line }}"
                        fill="none"
                        stroke="{{ $color }}"
                        stroke-width="2"
                        stroke-linejoin="round"
                        stroke-linecap="round"
                        vector-effect="non-scaling-stroke"
                    />
                @endif
            </svg>

            {{-- End marker with its surface ring, plus the one direct label this chart needs --}}
            @if ($pointCount > 0)
                @php $endPoint = $coordinates[$lastIndex]; @endphp

                <div
                    class="absolute size-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full ring-2 ring-card"
                    style="left: 100%; top: {{ $endPoint['y'] }}%; background-color: {{ $color }}"
                ></div>
                <span
                    class="absolute right-0 -translate-y-full pb-2 text-sm font-semibold text-foreground"
                    style="top: {{ $endPoint['y'] }}%"
                >{{ number_format($values[$lastIndex] ?? 0, 0, ',', '.') }}</span>
            @endif

            {{-- Hover layer: one hit column per point, wider than the mark itself --}}
            <div class="absolute inset-0 flex">
                @foreach ($labels as $index => $label)
                    <div class="group relative h-full min-w-0 flex-1">
                        <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 rounded-md border border-border bg-card px-2 py-1 text-xs whitespace-nowrap shadow-lg group-hover:block">
                            <p class="font-medium text-foreground">{{ $label['full'] }}</p>
                            <p class="text-muted-foreground">Totaal: <span class="font-medium text-foreground tabular-nums">{{ number_format($values[$index] ?? 0, 0, ',', '.') }}</span></p>
                        </div>
                        <div class="absolute inset-y-0 left-1/2 hidden w-px -translate-x-1/2 bg-border group-hover:block"></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div></div>

        <div class="flex min-w-0 gap-[2px]">
            @foreach ($labels as $index => $label)
                <div class="flex-1 text-center text-[11px] whitespace-nowrap text-muted-foreground tabular-nums">
                    {{ ($pointCount - 1 - $index) % $tickStep === 0 ? $label['short'] : '' }}
                </div>
            @endforeach
        </div>
    </div>
</div>
