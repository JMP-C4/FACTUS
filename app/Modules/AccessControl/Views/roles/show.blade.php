<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Rol: {{ $role->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('roles.edit', $role) }}" class="brutal-button">Editar permisos</a>
                <a href="{{ route('roles.index') }}" class="brutal-button">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        @php($assigned = $role->permissions->pluck('id')->all())

        @foreach($permissionsByModule as $module => $permissions)
            <div class="brutal-card mb-4">
                <h3 class="brutal-kicker mb-3 text-sm">{{ ucfirst($module) }}</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($permissions as $permission)
                        <span class="brutal-badge {{ in_array($permission->id, $assigned) ? 'bg-success text-white' : 'bg-muted text-white' }}">
                            {{ $permission->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
