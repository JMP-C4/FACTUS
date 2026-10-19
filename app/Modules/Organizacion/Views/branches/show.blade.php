<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">{{ $branch->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('sucursales.edit', $branch) }}" class="brutal-button">Editar</a>
                <a href="{{ route('sucursales.index') }}" class="brutal-button">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card">
            <div class="grid gap-4 sm:grid-cols-2">
                <div><p class="brutal-label text-[var(--color-text-soft)]">Código</p><p class="font-mono text-sm">{{ $branch->code }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Ciudad</p><p class="text-sm">{{ $branch->city ?? '—' }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Teléfono</p><p class="text-sm">{{ $branch->phone ?? '—' }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Dirección</p><p class="text-sm">{{ $branch->address ?? '—' }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Prefijo POS</p><p class="font-mono text-sm">{{ $branch->pos_prefix }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Rango</p><p class="font-mono text-sm">{{ $branch->resolution_from }} – {{ $branch->resolution_to }}</p></div>
                <div><p class="brutal-label text-[var(--color-text-soft)]">Tipo</p><p class="text-sm">{{ $branch->is_warehouse ? 'Bodega' : 'Punto de venta' }}</p></div>
                <div>
                    <p class="brutal-label text-[var(--color-text-soft)]">Estado</p>
                    @if($branch->is_active)
                        <span class="brutal-badge bg-success text-white">Activa</span>
                    @else
                        <span class="brutal-badge bg-muted text-white">Inactiva</span>
                    @endif
                </div>
                <div class="sm:col-span-2 flex gap-2">
                    @if($branch->is_main)<span class="brutal-badge bg-primary text-white">Principal</span>@endif
                    @if($branch->allows_negative_stock)<span class="brutal-badge bg-danger text-white">Permite stock negativo</span>@endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
