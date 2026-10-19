<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Roles y Permisos</h2>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Alcance</th>
                        <th>Permisos</th>
                        <th>Usuarios</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td class="font-semibold">
                                <a href="{{ route('roles.show', $role) }}" class="hover:text-primary">{{ $role->name }}</a>
                                @if($role->description)<p class="text-xs text-muted">{{ $role->description }}</p>@endif
                            </td>
                            <td><span class="brutal-badge bg-secondary text-white">{{ $role->scope_type }}</span></td>
                            <td class="font-mono">{{ $role->permissions_count }}</td>
                            <td class="font-mono">{{ $role->user_roles_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('roles.edit', $role) }}" class="brutal-button !px-3 !py-1.5 !text-[10px]">Editar permisos</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-muted">No hay roles registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
