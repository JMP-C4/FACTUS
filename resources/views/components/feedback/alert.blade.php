@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
    'icon' => true,
])

@php
    $variants = [
        'success' => ['bg-success', 'text-white'],
        'error' => ['bg-danger', 'text-white'],
        'danger' => ['bg-danger', 'text-white'],
        'warning' => ['bg-accent', 'text-ink'],
        'info' => ['bg-primary', 'text-white'],
        'neutral' => ['bg-surface', 'text-ink'],
    ];

    [$background, $foreground] = $variants[$type] ?? $variants['info'];

    $icons = [
        'success' => 'M22 4 12 14.01l-3-3',
        'error' => 'M12 8v4M12 16h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
        'danger' => 'M12 8v4M12 16h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
        'warning' => 'M12 9v4M12 17h.01',
        'info' => 'M12 16v-4M12 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
    ];

    $role = in_array($type, ['error', 'danger', 'warning'], true) ? 'alert' : 'status';
@endphp

<div
    @if ($dismissible)
        x-data="{ visible: true }"
        x-show="visible"
        x-transition.opacity.duration.200ms
    @endif
    role="{{ $role }}"
    {{ $attributes->merge(['class' => "brutal-alert {$background} {$foreground}"]) }}
>
    @if ($icon)
        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            @if (in_array($type, ['success'], true))
                <path d="M22 11.1V12a10 10 0 1 1-5.9-9.1" />
                <path d="{{ $icons[$type] }}" />
            @else
                <path d="{{ $icons[$type] ?? $icons['info'] }}" />
            @endif
        </svg>
    @endif

    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-display text-xs font-black uppercase tracking-brutal">{{ $title }}</p>
        @endif

        <div class="{{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>
    </div>

    @if ($dismissible)
        <button
            type="button"
            @click="visible = false"
            class="shrink-0 opacity-80 transition-opacity hover:opacity-100"
            aria-label="Cerrar aviso"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                <path d="M18 6 6 18M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
