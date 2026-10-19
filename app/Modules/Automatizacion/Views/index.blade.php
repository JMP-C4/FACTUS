<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="brutal-kicker text-[var(--color-text-soft)]">Operación</p>
                <h2 class="brutal-kicker text-lg">Automatización de procesos</h2>
            </div>
            <span class="brutal-badge bg-[var(--color-ink)] text-[var(--color-surface)]">Ejecución manual</span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <p class="mb-6 max-w-3xl text-sm text-[var(--color-text-soft)]">
            Tareas de mantenimiento y rutinas operativas que se ejecutan <strong>bajo demanda</strong>.
            Cada proceso inicia únicamente cuando presionas su botón; no se ejecuta nada de forma automática.
        </p>

        @if (session('automation_result'))
            <div class="brutal-card mb-6">
                <p class="brutal-kicker mb-3">Resultado de la última ejecución</p>

                <dl class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach (session('automation_result') as $metric => $value)
                        <div class="border-[2px] border-black bg-[var(--color-surface-muted)] px-3 py-2">
                            <dt class="text-[10px] uppercase tracking-[0.18em] text-[var(--color-text-soft)]">{{ $metric }}</dt>
                            <dd class="font-display text-lg font-black leading-tight">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif

        @if (empty($processes))
            <x-feedback.alert type="info" title="Sin procesos disponibles">
                Tu rol no tiene permisos para ejecutar procesos de automatización.
            </x-feedback.alert>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($processes as $process)
                    <div class="brutal-card flex flex-col justify-between">
                        <div>
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center border-[3px] border-black bg-[var(--color-accent)]">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="{{ $process->icon() }}" />
                                    </svg>
                                </span>

                                @if ($process->dangerous())
                                    <span class="brutal-badge bg-[var(--color-danger)] text-white">Modifica datos</span>
                                @endif
                            </div>

                            <h3 class="font-display text-base font-black uppercase leading-tight">{{ $process->label() }}</h3>
                            <p class="mt-2 text-sm text-[var(--color-text-soft)]">{{ $process->description() }}</p>
                        </div>

                        <div class="mt-6">
                            @if ($process->dangerous())
                                <x-confirm-dialog
                                    :name="'proc-'.$process->key()"
                                    :action="route('automatizacion.run', $process->key())"
                                    title="Confirmar ejecución"
                                    :message="'¿Ejecutar «'.$process->label().'»? Esta acción modifica datos.'"
                                    confirmLabel="Ejecutar"
                                >
                                    <x-slot:trigger>
                                        <button type="button" class="brutal-button w-full !bg-danger !text-white">
                                            Ejecutar proceso
                                        </button>
                                    </x-slot:trigger>
                                </x-confirm-dialog>
                            @else
                                <form method="POST" action="{{ route('automatizacion.run', $process->key()) }}">
                                    @csrf

                                    <button type="submit" class="brutal-button w-full">
                                        Ejecutar proceso
                                    </button>
                                </form>
                            @endif

                            <p class="mt-2 text-[10px] uppercase tracking-[0.18em] text-[var(--color-text-soft)]">
                                Requiere: {{ $process->permission() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
