<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">{{ $company->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('empresas.edit', $company) }}" class="brutal-button">Editar</a>
                <a href="{{ route('empresas.index') }}" class="brutal-button">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card mb-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div><p class="brutal-label text-[var(--color-text-soft)]">NIT</p><p class="font-mono text-sm">{{ $company->nit }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Régimen</p><p class="text-sm">{{ ucfirst($company->regime) }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Email</p><p class="text-sm">{{ $company->email ?? '—' }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Teléfono</p><p class="text-sm">{{ $company->phone ?? '—' }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Ciudad</p><p class="text-sm">{{ $company->city ?? '—' }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Dirección</p><p class="text-sm">{{ $company->address ?? '—' }}</p></div>
                <div>
                    <p class="brutal-label text-[var(--color-text-soft)]">Estado</p>
                    @if($company->is_active)
                        <span class="brutal-badge bg-success text-white">Activa</span>
                    @else
                        <span class="brutal-badge bg-muted text-white">Inactiva</span>
                    @endif
                </div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Usuarios / Sucursales</p><p class="font-mono text-sm">{{ $company->users_count }} / {{ $company->branches_count }}</p></div>
            </div>
        </div>

        <div class="brutal-card">
            <h3 class="brutal-kicker mb-4 text-sm">Sucursales</h3>
            <table class="brutal-table">
                <thead>
                    <tr><th>Nombre</th><th>Código</th><th>Ciudad</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse($company->branches as $branch)
                        <tr>
                            <td>{{ $branch->name }}</td>
                            <td class="font-mono text-xs">{{ $branch->code }}</td>
                            <td>{{ $branch->city ?? '—' }}</td>
                            <td>
                                @if($branch->is_active)
                                    <span class="brutal-badge bg-success text-white">Activa</span>
                                @else
                                    <span class="brutal-badge bg-muted text-white">Inactiva</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-muted">Sin sucursales registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
