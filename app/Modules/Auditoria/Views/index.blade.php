<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Auditoría / Logs del sistema</h2>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('auditoria.index') }}" class="flex flex-wrap gap-4">
                <div class="min-w-[200px] flex-1">
                    <label class="brutal-label mb-1 block">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Descripción, módulo o evento..." class="brutal-input">
                </div>

                <div class="min-w-[140px]">
                    <label class="brutal-label mb-1 block">Módulo</label>
                    <select name="module" class="brutal-input">
                        <option value="">Todos</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>{{ $module }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[140px]">
                    <label class="brutal-label mb-1 block">Evento</label>
                    <select name="event" class="brutal-input">
                        <option value="">Todos</option>
                        @foreach($events as $event)
                            <option value="{{ $event }}" {{ request('event') === $event ? 'selected' : '' }}>{{ $event }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Desde</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="brutal-input">
                </div>

                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Hasta</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="brutal-input">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="brutal-button">Filtrar</button>
                    <a href="{{ route('auditoria.index') }}" class="brutal-button">Limpiar</a>
                </div>
            </form>
        </div>

        {{-- Tabla --}}
        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Módulo</th>
                        <th>Evento</th>
                        <th>Descripción</th>
                        <th>IP</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="font-mono text-xs">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->user?->name ?? 'Sistema' }}</td>
                            <td><span class="brutal-badge bg-secondary text-white">{{ $log->module }}</span></td>
                            <td><span class="brutal-badge bg-muted text-white">{{ $log->event }}</span></td>
                            <td class="max-w-md">{{ $log->description }}</td>
                            <td class="font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                            <td class="text-right">
                                <a
                                    href="{{ route('auditoria.show', $log) }}"
                                    class="brutal-button !px-2.5 !py-1.5"
                                    title="Ver"
                                    aria-label="Ver registro de auditoría"
                                >
                                    <x-action-icon name="view" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-muted">No hay eventos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
