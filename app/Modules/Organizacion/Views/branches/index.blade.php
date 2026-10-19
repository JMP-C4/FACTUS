<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Sucursales</h2>
            <a href="{{ route('sucursales.create') }}" class="brutal-button">Nueva Sucursal</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('sucursales.index') }}" class="flex flex-wrap gap-4">
                <div class="min-w-[200px] flex-1">
                    <label class="brutal-label mb-1 block">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre, código o ciudad..." class="brutal-input">
                </div>
                <div class="min-w-[120px]">
                    <label class="brutal-label mb-1 block">Estado</label>
                    <select name="is_active" class="brutal-input">
                        <option value="">Todas</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Activas</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactivas</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="brutal-button">Filtrar</button>
                </div>
            </form>
        </div>

        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Ciudad</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                        <tr>
                            <td class="font-semibold">
                                <a href="{{ route('sucursales.show', $branch) }}" class="hover:text-primary">{{ $branch->name }}</a>
                                @if($branch->is_main)<span class="brutal-badge ml-2 bg-primary text-white">Principal</span>@endif
                            </td>
                            <td class="font-mono text-xs">{{ $branch->code }}</td>
                            <td>{{ $branch->city ?? '—' }}</td>
                            <td>
                                @if($branch->is_warehouse)
                                    <span class="brutal-badge bg-secondary text-white">Bodega</span>
                                @else
                                    <span class="brutal-badge bg-accent text-white">POS</span>
                                @endif
                            </td>
                            <td>
                                @if($branch->is_active)
                                    <span class="brutal-badge bg-success text-white">Activa</span>
                                @else
                                    <span class="brutal-badge bg-muted text-white">Inactiva</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('sucursales.edit', $branch) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Editar"
                                        aria-label="Editar {{ $branch->name }}"
                                    >
                                        <x-action-icon name="edit" />
                                    </a>
                                    <x-confirm-dialog
                                        name="toggle-branch-{{ $branch->id }}"
                                        :title="$branch->is_active ? 'Desactivar sucursal' : 'Activar sucursal'"
                                        :message="$branch->is_active ? '¿Desactivar esta sucursal? No podrá operar hasta reactivarla.' : '¿Activar esta sucursal?'"
                                        :confirm-label="$branch->is_active ? 'Desactivar' : 'Activar'"
                                        :variant="$branch->is_active ? 'danger' : 'success'"
                                        action="{{ route('sucursales.toggleActive', $branch) }}"
                                        method="PATCH"
                                    >
                                        <x-slot:trigger>
                                            <button
                                                type="button"
                                                class="brutal-button !px-2.5 !py-1.5 {{ $branch->is_active ? '!bg-danger !text-white' : '!bg-success !text-white' }}"
                                                title="{{ $branch->is_active ? 'Desactivar' : 'Activar' }}"
                                                aria-label="{{ $branch->is_active ? 'Desactivar' : 'Activar' }} {{ $branch->name }}"
                                            >
                                                <x-action-icon name="toggle" />
                                            </button>
                                        </x-slot:trigger>
                                    </x-confirm-dialog>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-muted">No se encontraron sucursales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $branches->withQueryString()->links() }}</div>
    </div>
</x-app-layout>
