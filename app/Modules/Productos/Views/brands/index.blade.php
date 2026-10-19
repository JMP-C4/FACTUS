<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Marcas</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('productos.index') }}" class="brutal-button">
                    Productos
                </a>
                <a href="{{ route('productos.marcas.create') }}" class="brutal-button !bg-primary !text-white">
                    Nueva Marca
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('productos.marcas.index') }}" class="flex flex-wrap gap-4">
                <div class="min-w-[200px] flex-1">
                    <label class="brutal-label mb-1 block">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="brutal-input" placeholder="Nombre">
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
                        <th>Productos</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                        <tr>
                            <td class="font-bold">{{ $brand->name }}</td>
                            <td class="font-mono">{{ $brand->products_count }}</td>
                            <td>
                                <span class="brutal-button !px-2 !py-1 !text-[10px] {{ $brand->is_active ? '!bg-success !text-white' : '!bg-muted !text-white' }}">
                                    {{ $brand->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('productos.marcas.edit', $brand) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Editar"
                                        aria-label="Editar {{ $brand->name }}"
                                    >
                                        <x-action-icon name="edit" />
                                    </a>
                                    <form method="POST" action="{{ route('productos.marcas.toggleActive', $brand) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="brutal-button !px-2.5 !py-1.5"
                                            title="{{ $brand->is_active ? 'Desactivar' : 'Activar' }}"
                                            aria-label="{{ $brand->is_active ? 'Desactivar' : 'Activar' }} {{ $brand->name }}"
                                        >
                                            <x-action-icon name="toggle" />
                                        </button>
                                    </form>
                                    <x-confirm-dialog
                                        name="delete-brand-{{ $brand->id }}"
                                        title="Eliminar marca"
                                        message="¿Eliminar esta marca? Esta acción es irreversible."
                                        confirm-label="Eliminar"
                                        variant="danger"
                                        action="{{ route('productos.marcas.destroy', $brand) }}"
                                        method="DELETE"
                                    >
                                        <x-slot:trigger>
                                            <button type="button" class="brutal-button !px-2.5 !py-1.5 !bg-danger !text-white" title="Eliminar" aria-label="Eliminar {{ $brand->name }}">
                                                <x-action-icon name="delete" />
                                            </button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-muted">
                                No se encontraron marcas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $brands->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
