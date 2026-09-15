{{-- Shown instead of a page whose feature is switched off in "Site Settings". --}}
@props([
    'title',
    'text',
])

<div class="container mx-auto px-4 py-24 text-center">
    <h1 class="mb-4 text-3xl font-bold">{{ $title }}</h1>
    <p class="text-muted-foreground">{{ $text }}</p>
</div>
