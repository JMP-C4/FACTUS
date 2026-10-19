<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">{{ $user->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('usuarios.edit', $user) }}" class="brutal-button">Editar</a>
                <a href="{{ route('usuarios.index') }}" class="brutal-button">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card mb-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div><p class="brutal-label text-[var(--color-text-soft)]">Email</p><p class="text-sm">{{ $user->email }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Compañía</p><p class="text-sm">{{ $user->company?->name ?? 'Global' }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Sucursal</p><p class="text-sm">{{ $user->branch?->name ?? 'Todas' }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Alcance</p><p class="text-sm">{{ $user->isGloballyScoped() ? 'Global' : ($user->isBranchScoped() ? 'Sucursal' : 'Compañía') }}</p></div>
            </div>
        </div>

        <div class="brutal-card">
            <h3 class="brutal-kicker mb-4 text-sm">Roles y Permisos</h3>
            @forelse($user->roles as $role)
                <div class="mb-4">
                    <p class="font-semibold">{{ $role->name }} <span class="text-xs text-muted">({{ $role->scope_type }})</span></p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse($role->permissions as $permission)
                            <span class="brutal-badge bg-accent text-white">{{ $permission->name }}</span>
                        @empty
                            <span class="text-muted">Sin permisos asignados.</span>
                        @endforelse
                    </div>
                </div>
            @empty
                <p class="text-muted">Este usuario no tiene roles asignados.</p>
            @endforelse
        </div>

        @if($user->directPermissions->isNotEmpty())
            <div class="brutal-card mt-6">
                <h3 class="brutal-kicker mb-1 text-xs">Permisos adicionales (solo este usuario)</h3>
                <p class="mb-3 text-xs text-muted">
                    Asignados directamente a {{ $user->name }}; no provienen del rol ni afectan a otros usuarios.
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach($user->directPermissions as $permission)
                        <span class="brutal-badge bg-success text-white">{{ $permission->name }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
