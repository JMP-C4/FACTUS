<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Permisos de: {{ $role->name }}</h2>
            <a href="{{ route('roles.index') }}" class="brutal-button">Volver</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')

            @php($assigned = $role->permissions->pluck('id')->all())

            @foreach($permissionsByModule as $module => $permissions)
                <div class="brutal-card mb-4" x-data="{ all: {{ $permissions->every(fn ($p) => in_array($p->id, $assigned)) ? 'true' : 'false' }} }">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="brutal-kicker text-sm">{{ ucfirst($module) }}</h3>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="h-4 w-4" x-model="all"
                                @change="$root.querySelectorAll('.permission-checkbox').forEach(c => c.checked = all)">
                            <span class="brutal-label">Todos</span>
                        </label>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($permissions as $permission)
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="permission-checkbox mt-1 h-4 w-4"
                                    {{ in_array($permission->id, $assigned) ? 'checked' : '' }}>
                                <span>
                                    <span class="block text-sm font-semibold">{{ $permission->name }}</span>
                                    <span class="block text-xs text-muted">{{ $permission->description }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-end gap-4 border-t-[2px] border-ink pt-4">
                <a href="{{ route('roles.index') }}" class="brutal-button">Cancelar</a>
                <button type="submit" class="brutal-button !bg-primary !text-white">Guardar Permisos</button>
            </div>
        </form>
    </div>
</x-app-layout>
