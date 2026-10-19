<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Impuestos (IVA)</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('productos.index') }}" class="brutal-button">
                    Productos
                </a>
                <a href="{{ route('productos.impuestos.create') }}" class="brutal-button !bg-primary !text-white">
                    Nuevo Impuesto
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('productos.impuestos.index') }}" class="flex flex-wrap gap-4">
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
                        <th>Tarifa</th>
                        <th>Tipo</th>
                        <th>Alcance</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxes as $tax)
                        <tr>
                            <td class="font-bold">{{ $tax->name }}</td>
                            <td class="font-mono text-xs">{{ $tax->code }}</td>
                            <td class="font-mono font-bold">{{ number_format((float) $tax->rate, 2, ',', '.') }}%</td>
                            <td>
                                <span class="brutal-badge bg-muted text-white">
                                    {{ $tax->type === 'vat' ? 'IVA' : ($tax->type === 'exempt' ? 'Exento' : 'Excluido') }}
                                </span>
                            </td>
                            <td class="text-xs">{{ $tax->company_id === null ? 'Global' : 'Compañía' }}</td>
                            <td>
                                <span class="brutal-badge bg-{{ $tax->is_active ? 'success' : 'muted' }} text-white">
                                    {{ $tax->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($tax->company_id === null)
                                        <span class="text-[10px] uppercase text-muted">Solo lectura</span>
                                    @else
                                        <a
                                            href="{{ route('productos.impuestos.edit', $tax) }}"
                                            class="brutal-button !px-2.5 !py-1.5"
                                            title="Editar"
                                            aria-label="Editar {{ $tax->name }}"
                                        >
                                            <x-action-icon name="edit" />
                                        </a>
                                        <form method="POST" action="{{ route('productos.impuestos.toggleActive', $tax) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="brutal-button !px-2.5 !py-1.5"
                                                title="{{ $tax->is_active ? 'Desactivar' : 'Activar' }}"
                                                aria-label="{{ $tax->is_active ? 'Desactivar' : 'Activar' }} {{ $tax->name }}"
                                            >
                                                <x-action-icon name="toggle" />
                                            </button>
                                        </form>
                                        <x-confirm-dialog
                                            name="delete-tax-{{ $tax->id }}"
                                            title="Eliminar impuesto"
                                            message="¿Eliminar este impuesto? Esta acción es irreversible."
                                            confirm-label="Eliminar"
                                            variant="danger"
                                            action="{{ route('productos.impuestos.destroy', $tax) }}"
                                            method="DELETE"
                                        >
                                            <x-slot:trigger>
                                                <button type="button" class="brutal-button !px-2.5 !py-1.5 !bg-danger !text-white" title="Eliminar" aria-label="Eliminar {{ $tax->name }}">
                                                    <x-action-icon name="delete" />
                                                </button>
                                            </x-slot:trigger>
                                        </x-confirm-dialog>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-muted">
                                No se encontraron impuestos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $taxes->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
