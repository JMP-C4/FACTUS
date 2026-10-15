@props([
    'label',
    'value',
    'caption',
    'tone' => 'blue',
])

@php
    // Elegimos sombra por tono para que la tarjeta pueda reutilizarse
    // en varios dashboards sin duplicar clases manuales.
    $shadowClass = match ($tone) {
        'amber' => 'shadow-brutal-amber',
        'black' => 'shadow-brutal-black',
        default => 'shadow-brutal-blue',
    };
@endphp

<article class="brutal-stat {{ $shadowClass }}">
    <div class="space-y-3">
        <p class="brutal-kicker text-[var(--color-text-soft)]">{{ $label }}</p>
        <p class="font-display text-4xl font-black uppercase leading-none">{{ $value }}</p>
    </div>

    <p class="max-w-[18rem] text-sm leading-6 text-[var(--color-text-muted)]">
        {{ $caption }}
    </p>
</article>
