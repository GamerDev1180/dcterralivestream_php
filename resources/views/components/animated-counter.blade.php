{{--
    Counts up from 0 to the given number once it scrolls into view.
    Usage: <x-animated-counter :value="24" />
--}}
@props([
    'value' => 0,
    'duration' => 1,
])

<span
    x-data="{
        display: 0,
        countUp() {
            const target = {{ (int) $value }};
            const duration = {{ (float) $duration }} * 1000;
            const start = performance.now();
            const step = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                this.display = Math.round(target * (1 - Math.pow(1 - progress, 3)));
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        },
    }"
    x-intersect.once="countUp()"
    x-text="display.toLocaleString('nl-NL')"
    {{ $attributes }}
>{{ (int) $value }}</span>
