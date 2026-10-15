<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Movimientos de Inventario</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventario.entradas.create') }}" class="brutal-button !bg-success !text-white">
                    Nueva Entrada
                </a>
                <a href="{{ route('inventario.ajustes.create') }}" class="brutal-button !bg-accent !text-ink">
                    Nuevo Ajuste
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('inventario.movimientos.index') }}" class="flex flex-wrap gap-4">
                <div class="min-w-[180px]">
                    <label class="brutal-label mb-1 block">Almacén</label>
                    <select name="warehouse_id" class="brutal-input">
                        <option value="">Todos</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Tipo</label>
                    <select name="movement_type" class="brutal-input">
                        <option value="">Todos</option>
                        <option value="entry" {{ request('movement_type') === 'entry' ? 'selected' : '' }}>Entrada</option>
                        <option value="exit" {{ request('movement_type') === 'exit' ? 'selected' : '' }}>Salida</option>
                        <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }}>Ajuste</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="brutal-button">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabla de movimientos --}}
        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Producto</th>
                        <th>Almacén</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td class="text-xs">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @switch($movement->movement_type)
                                    @case('entry')
                                        <span class="brutal-badge bg-success text-white">Entrada</span>
                                        @break
                                    @case('exit')
                                        <span class="brutal-badge bg-danger text-white">Salida</span>
                                        @break
                                    @case('adjustment')
                                        <span class="brutal-badge bg-accent text-ink">Ajuste</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="font-semibold">{{ $movement->product?->name ?? '—' }}</td>
                            <td>{{ $movement->warehouse?->name ?? '—' }}</td>
                            <td class="font-mono {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                            </td>
                            <td class="text-sm text-muted">{{ $movement->reason ?? '—' }}</td>
                            <td>{{ $movement->performer?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-muted">
                                No se encontraron movimientos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-6">
            {{ $movements->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
