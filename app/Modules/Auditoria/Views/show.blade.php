<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Evento de Auditoría #{{ $log->id }}</h2>
            <a href="{{ route('auditoria.index') }}" class="brutal-button">Volver</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="brutal-label text-[var(--color-text-soft)]">Fecha</p>
                    <p class="font-mono text-sm">{{ $log->created_at?->format('d/m/Y H:i:s') }}</p>
                </div>
                <div>
                    <p class="brutal-label text-[var(--color-text-soft)]">Usuario</p>
                    <p class="text-sm">{{ $log->user?->name ?? 'Sistema' }}</p>
                </div>
                <div>
                    <p class="brutal-label text-[var(--color-text-soft)]">Módulo</p>
                    <p class="text-sm">{{ $log->module }}</p>
                </div>
                <div>
                    <p class="brutal-label text-[var(--color-text-soft)]">Evento</p>
                    <p class="text-sm">{{ $log->event }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="brutal-label text-[var(--color-text-soft)]">Descripción</p>
                    <p class="text-sm">{{ $log->description }}</p>
                </div>
                <div>
                    <p class="brutal-label text-[var(--color-text-soft)]">IP</p>
                    <p class="font-mono text-sm">{{ $log->ip_address ?? '—' }}</p>
                </div>
                <div>
                    <p class="brutal-label text-[var(--color-text-soft)]">Sujeto</p>
                    <p class="font-mono text-xs">{{ $log->subject_type ?? '—' }} @if($log->subject_id) #{{ $log->subject_id }} @endif</p>
                </div>
            </div>

            @if($log->properties)
                <div class="border-t-[2px] border-ink pt-4">
                    <p class="brutal-label mb-2 text-[var(--color-text-soft)]">Propiedades</p>
                    <pre class="overflow-x-auto border-[2px] border-ink bg-[var(--color-bg)] p-3 text-xs">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif

            @if($log->user_agent)
                <div class="border-t-[2px] border-ink pt-4">
                    <p class="brutal-label mb-1 text-[var(--color-text-soft)]">User Agent</p>
                    <p class="text-xs text-[var(--color-text-muted)]">{{ $log->user_agent }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
