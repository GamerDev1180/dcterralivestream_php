{{--
    Stacked column chart. Built from plain elements rather than a scaled <svg>
    so the marks stay crisp and the labels keep their real font size.

    @param array<int, array{short: string, full: string}> $labels
    @param array<int, array{name: string, color: string, values: array<int, int>}> $series
--}}
@props([
    'labels' => [],
    'series' => [],
    'height' => 180,
    'unit' => '',
])

@php
    $columnCount = count($labels);
    $totals = [];

    foreach (array_keys($labels) as $columnIndex) {
        $totals[$columnIndex] = array_sum(array_column(array_column($series, 'values'), $columnIndex));
    }

    $max = max(1, ...$totals ?: [1]);

    // Show at most eight x-axis ticks so the dates never collide.
    $tickStep = max(1, (int) ceil($columnCount / 8));
@endphp

<div {{ $attributes->class('space-y-3') }}>
    @if (count($series) > 1)
        <x-charts.legend :series="$series" />
    @endif

    <div class="flex items-end gap-[2px]" style="height: {{ $height }}px">
        @foreach ($labels as $columnIndex => $label)
            @php
                $topSeriesIndex = null;

                foreach ($series as $seriesIndex => $item) {
                    if (($item['values'][$columnIndex] ?? 0) > 0) {
                        $topSeriesIndex = $seriesIndex;
                    }
                }
            @endphp

            <div class="group relative flex h-full min-w-0 flex-1 flex-col justify-end rounded-sm hover:bg-muted/50">
                <div class="mx-auto flex w-full max-w-6 flex-col justify-end gap-[2px]" style="height: 100%">
                    @foreach (array_reverse($series, true) as $seriesIndex => $item)
                        @php $value = $item['values'][$columnIndex] ?? 0; @endphp

                        @if ($value > 0)
                            <div
                                @class(['w-full', 'rounded-t-[4px]' => $seriesIndex === $topSeriesIndex])
                                style="height: {{ round($value / $max * 100, 2) }}%; background-color: {{ $item['color'] }}"
                            ></div>
                        @endif
                    @endforeach
                </div>

                {{-- Hover layer --}}
                <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 rounded-md border border-border bg-card px-2 py-1 text-xs whitespace-nowrap shadow-lg group-hover:block">
                    <p class="font-medium text-foreground">{{ $label['full'] }}</p>
                    @foreach ($series as $item)
                        <p class="flex items-center gap-1.5 text-muted-foreground">
                            <span class="size-2 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                            {{ $item['name'] }}
                            <span class="ml-auto pl-2 font-medium text-foreground tabular-nums">{{ $item['values'][$columnIndex] ?? 0 }}{{ $unit }}</span>
                        </p>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex gap-[2px] border-t border-border pt-1.5">
        @foreach ($labels as $columnIndex => $label)
            <div class="flex-1 text-center text-[11px] whitespace-nowrap text-muted-foreground tabular-nums">
                {{ ($columnCount - 1 - $columnIndex) % $tickStep === 0 ? $label['short'] : '' }}
            </div>
        @endforeach
    </div>

    <details class="group/table">
        <summary class="cursor-pointer text-xs text-muted-foreground hover:text-foreground">Tabelweergave</summary>

        <table class="mt-2 w-full text-xs">
            <thead>
                <tr class="text-left text-muted-foreground">
                    <th class="py-1 font-medium">Periode</th>
                    @foreach ($series as $item)
                        <th class="py-1 text-right font-medium">{{ $item['name'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($labels as $columnIndex => $label)
                    <tr class="border-t border-border">
                        <td class="py-1 text-muted-foreground">{{ $label['full'] }}</td>
                        @foreach ($series as $item)
                            <td class="py-1 text-right tabular-nums">{{ $item['values'][$columnIndex] ?? 0 }}{{ $unit }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </details>
</div>
