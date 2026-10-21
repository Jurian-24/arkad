@props([
    'variant' => 'secure',
])

@php
    $variants = [
        'yield' => 'bg-gold/10 text-gold',
        'secure' => 'bg-sovereign/10 text-sovereign',
        'risk' => 'bg-terracotta/10 text-terracotta',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center rounded-sm px-2 py-1 text-xs font-semibold',
    $variants[$variant] ?? $variants['secure'],
]) }}>
    {{ $slot }}
</span>
