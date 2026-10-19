<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Clientes</h2>
            <a href="{{ route('clientes.create') }}" class="brutal-button">
                Nuevo Cliente
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="brutal-label mb-1 block">Buscar</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nombre, documento, teléfono o email..."
                        class="brutal-input"
                    >
                </div>

                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Estado</label>
                    <select name="is_facturable" class="brutal-input">
                        <option value="">Todos</option>
                        <option value="1" {{ request('is_facturable') === '1' ? 'selected' : '' }}>Facturables</option>
                        <option value="0" {{ request('is_facturable') === '0' ? 'selected' : '' }}>Incompletos</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="brutal-button">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabla de clientes --}}
        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="font-semibold">
                                <a href="{{ route('clientes.show', $customer) }}" class="hover:text-primary">
                                    {{ $customer->display_name }}
                                </a>
                            </td>
                            <td class="font-mono text-xs">{{ $customer->document_number }}</td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td>{{ $customer->email ?? '—' }}</td>
                            <td>
                                @if($customer->is_facturable)
                                    <span class="brutal-badge bg-success text-white">Facturable</span>
                                @else
                                    <span class="brutal-badge bg-accent text-ink">Incompleto</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('clientes.show', $customer) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Ver"
                                        aria-label="Ver {{ $customer->display_name }}"
                                    >
                                        <x-action-icon name="view" />
                                    </a>
                                    <a
                                        href="{{ route('clientes.edit', $customer) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Editar"
                                        aria-label="Editar {{ $customer->display_name }}"
                                    >
                                        <x-action-icon name="edit" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-muted">
                                No se encontraron clientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-6">
            {{ $customers->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
