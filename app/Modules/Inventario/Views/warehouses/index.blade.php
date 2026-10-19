<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Almacenes</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventario.movimientos.index') }}" class="brutal-button">
                    Movimientos
                </a>
                <a href="{{ route('inventario.almacenes.create') }}" class="brutal-button !bg-primary !text-white">
                    Nuevo Almacén
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('inventario.almacenes.index') }}" class="flex flex-wrap gap-4">
                <div class="min-w-[200px] flex-1">
                    <label class="brutal-label mb-1 block">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="brutal-input" placeholder="Nombre o código">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="brutal-button">Filtrar</button>
                </div>
            </form>
        </div>

        {{-- Tabla --}}
        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Productos</th>
                        <th>Predeterminado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr>
                            <td class="font-bold">{{ $warehouse->name }}</td>
                            <td class="font-mono text-xs text-muted">{{ $warehouse->code }}</td>
                            <td class="font-mono">{{ $warehouse->stocks_count }}</td>
                            <td>
                                @if($warehouse->is_default)
                                    <span class="brutal-button !px-2 !py-1 !text-[10px] !bg-success !text-white">Predeterminado</span>
                                @else
                                    <form method="POST" action="{{ route('inventario.almacenes.setDefault', $warehouse) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="brutal-button !px-3 !py-1.5 !text-[10px]">
                                            Marcar predeterminado
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('inventario.almacenes.edit', $warehouse) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Editar"
                                        aria-label="Editar {{ $warehouse->name }}"
                                    >
                                        <x-action-icon name="edit" />
                                    </a>
                                    <x-confirm-dialog
                                        name="delete-warehouse-{{ $warehouse->id }}"
                                        title="Eliminar almacén"
                                        message="¿Eliminar este almacén? Esta acción es irreversible."
                                        confirm-label="Eliminar"
                                        variant="danger"
                                        action="{{ route('inventario.almacenes.destroy', $warehouse) }}"
                                        method="DELETE"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="brutal-button !px-2.5 !py-1.5 !bg-danger !text-white" title="Eliminar" aria-label="Eliminar {{ $warehouse->name }}">
                                                <x-action-icon name="delete" />
                                            </button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-muted">
                                No se encontraron almacenes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $warehouses->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
