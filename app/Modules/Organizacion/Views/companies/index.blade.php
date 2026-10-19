<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Compañías</h2>
            <a href="{{ route('empresas.create') }}" class="brutal-button">Nueva Compañía</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('empresas.index') }}" class="flex flex-wrap gap-4">
                <div class="min-w-[200px] flex-1">
                    <label class="brutal-label mb-1 block">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre, NIT o email..." class="brutal-input">
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
                        <th>NIT</th>
                        <th>Ciudad</th>
                        <th>Sucursales</th>
                        <th>Usuarios</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td class="font-semibold">
                                <a href="{{ route('empresas.show', $company) }}" class="hover:text-primary">{{ $company->name }}</a>
                            </td>
                            <td class="font-mono text-xs">{{ $company->nit }}</td>
                            <td>{{ $company->city ?? '—' }}</td>
                            <td class="font-mono">{{ $company->branches_count }}</td>
                            <td class="font-mono">{{ $company->users_count }}</td>
                            <td>
                                @if($company->is_active)
                                    <span class="brutal-badge bg-success text-white">Activa</span>
                                @else
                                    <span class="brutal-badge bg-muted text-white">Inactiva</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('empresas.edit', $company) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Editar"
                                        aria-label="Editar {{ $company->name }}"
                                    >
                                        <x-action-icon name="edit" />
                                    </a>
                                    <x-confirm-dialog
                                        name="toggle-company-{{ $company->id }}"
                                        :title="$company->is_active ? 'Desactivar compañía' : 'Activar compañía'"
                                        :message="$company->is_active ? '¿Desactivar esta compañía? Sus usuarios podrían perder acceso.' : '¿Activar esta compañía?'"
                                        :confirm-label="$company->is_active ? 'Desactivar' : 'Activar'"
                                        :variant="$company->is_active ? 'danger' : 'success'"
                                        action="{{ route('empresas.toggleActive', $company) }}"
                                        method="PATCH"
                                    >
                                        <x-slot:trigger>
                                            <button
                                                type="button"
                                                class="brutal-button !px-2.5 !py-1.5 {{ $company->is_active ? '!bg-danger !text-white' : '!bg-success !text-white' }}"
                                                title="{{ $company->is_active ? 'Desactivar' : 'Activar' }}"
                                                aria-label="{{ $company->is_active ? 'Desactivar' : 'Activar' }} {{ $company->name }}"
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
                            <td colspan="7" class="py-8 text-center text-muted">No se encontraron compañías.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $companies->withQueryString()->links() }}</div>
    </div>
</x-app-layout>
