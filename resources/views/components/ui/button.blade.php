@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-md transition-colors focus-visible:outline-2 focus-visible:outline-offset-2';

    $variants = [
        'primary' => 'bg-sovereign text-white border border-sovereign-hover hover:bg-sovereign-hover hover:shadow-[0_0_0_1px_#DFB135]',
        'secondary' => 'bg-gold/10 text-basalt border border-gold hover:bg-gold/20',
        'ghost' => 'bg-transparent text-basalt border border-transparent hover:underline hover:decoration-gold hover:decoration-2 hover:underline-offset-4',
    ];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([$base, $variants[$variant] ?? $variants['primary']]) }}
>
    {{ $slot }}
</button>
