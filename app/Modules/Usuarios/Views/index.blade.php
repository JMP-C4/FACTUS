<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Usuarios</h2>
            @if(auth()->user()->hasPermission('users.create'))
                <a href="{{ route('usuarios.create') }}" class="brutal-button">Nuevo Usuario</a>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('usuarios.index') }}" class="flex flex-wrap gap-4">
                <div class="min-w-[200px] flex-1">
                    <label class="brutal-label mb-1 block">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre o email..." class="brutal-input">
                </div>
                <div class="min-w-[160px]">
                    <label class="brutal-label mb-1 block">Rol</label>
                    <select name="role" class="brutal-input">
                        <option value="">Todos</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
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
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Compañía</th>
                        <th>Sucursal</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $roleColors = [
                                'super_admin' => 'bg-danger text-white',
                                'admin' => 'bg-primary text-white',
                                'supervisor' => 'bg-accent text-ink',
                                'cashier' => 'bg-success text-white',
                            ];
                        @endphp
                        <tr>
                            <td class="font-semibold">
                                <a href="{{ route('usuarios.show', $user) }}" class="hover:text-primary">{{ $user->name }}</a>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="brutal-badge {{ $roleColors[$role->name] ?? 'bg-ink text-white' }}">{{ $role->name }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>{{ $user->company?->name ?? 'Global' }}</td>
                            <td>{{ $user->branch?->name ?? 'Todas' }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('usuarios.show', $user) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Ver"
                                        aria-label="Ver {{ $user->name }}"
                                    >
                                        <x-action-icon name="view" />
                                    </a>
                                    <a
                                        href="{{ route('usuarios.edit', $user) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Editar"
                                        aria-label="Editar {{ $user->name }}"
                                    >
                                        <x-action-icon name="edit" />
                                    </a>
                                    @if(! $user->is(auth()->user()))
                                        <x-confirm-dialog
                                            name="delete-user-{{ $user->id }}"
                                            title="Eliminar usuario"
                                            message="¿Eliminar a {{ $user->name }}? Esta acción no se puede deshacer."
                                            confirm-label="Eliminar"
                                            action="{{ route('usuarios.destroy', $user) }}"
                                            method="DELETE"
                                        >
                                            <x-slot:trigger>
                                                <button
                                                    type="button"
                                                    class="brutal-button !bg-danger !px-2.5 !py-1.5 !text-white"
                                                    title="Eliminar"
                                                    aria-label="Eliminar {{ $user->name }}"
                                                >
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
                            <td colspan="6" class="py-8 text-center text-muted">No se encontraron usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $users->withQueryString()->links() }}</div>
    </div>
</x-app-layout>
