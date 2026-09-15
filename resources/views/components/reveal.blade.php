{{--
    Fades and slides its content in once it scrolls into view (uses Alpine's x-intersect, which ships with Livewire).
    Usage: <x-reveal :delay="0.1"> ... </x-reveal>
--}}
@props([
    'delay' => 0,
])

<div
    x-data="{ shown: false }"
    x-intersect.once="shown = true"
    :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'"
    style="transition-delay: {{ $delay }}s"
    {{ $attributes->class('transition duration-500 ease-out motion-reduce:transition-none') }}
>
    {{ $slot }}
</div>
