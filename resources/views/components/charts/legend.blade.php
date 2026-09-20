{{--
    Identity for every chart with two or more series. Text keeps the muted ink
    token; only the swatch beside it carries the series colour.

    @param array<int, array{name: string, color: string}> $series
--}}
@props(['series' => []])

<ul {{ $attributes->class('flex flex-wrap items-center gap-x-4 gap-y-1') }}>
    @foreach ($series as $item)
        <li class="flex items-center gap-1.5 text-xs text-muted-foreground">
            <span class="size-2.5 rounded-full" style="background-color: {{ $item['color'] }}"></span>
            {{ $item['name'] }}
        </li>
    @endforeach
</ul>
