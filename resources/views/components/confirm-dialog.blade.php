@props([
    'name',
    'title' => 'Confirmar acción',
    'subtitle' => null,
    'message' => '¿Estás seguro de continuar?',
    'confirmLabel' => 'Confirmar',
    'cancelLabel' => 'Cancelar',
    'variant' => 'danger',
    'action' => null,
    'method' => 'POST',
    'maxWidth' => 'md',
    'closeable' => true,
])

@php
    $confirmClasses = match ($variant) {
        'danger' => '!bg-danger !text-white',
        'success' => '!bg-success !text-white',
        default => '!bg-primary !text-white',
    };
@endphp

<div class="contents">
    <span x-data class="contents" x-on:click="$dispatch('open-dialog', '{{ $name }}')">
        {{ $trigger }}
    </span>

    <x-dialog
        :name="$name"
        :title="$title"
        :subtitle="$subtitle"
        :max-width="$maxWidth"
        :closeable="$closeable"
    >
        @if ($action)
            <form method="POST" action="{{ $action }}" class="space-y-5">
                @csrf

                @if (! in_array(strtoupper($method), ['GET', 'POST'], true))
                    @method($method)
                @endif

                <div class="text-sm">
                    @if ($slot->isNotEmpty())
                        {{ $slot }}
                    @else
                        {{ $message }}
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" class="brutal-button" x-on:click="$dispatch('close-dialog', '{{ $name }}')">
                        {{ $cancelLabel }}
                    </button>

                    <button type="submit" class="brutal-button {{ $confirmClasses }}">
                        {{ $confirmLabel }}
                    </button>
                </div>
            </form>
        @else
            <div class="space-y-5">
                <div class="text-sm">
                    @if ($slot->isNotEmpty())
                        {{ $slot }}
                    @else
                        {{ $message }}
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" class="brutal-button" x-on:click="$dispatch('close-dialog', '{{ $name }}')">
                        {{ $cancelLabel }}
                    </button>

                    <button
                        type="button"
                        class="brutal-button {{ $confirmClasses }}"
                        x-on:click="$dispatch('close-dialog', '{{ $name }}'); $dispatch('confirmed', '{{ $name }}')"
                    >
                        {{ $confirmLabel }}
                    </button>
                </div>
            </div>
        @endif
    </x-dialog>
</div>
