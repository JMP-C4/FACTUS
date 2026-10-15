<x-app-layout>
    <x-slot name="header">
        <h2 class="brutal-kicker text-lg">Reporte de Inventario</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="min-w-[180px]">
                    <label class="brutal-label mb-1 block">Almacén</label>
                    <select name="warehouse_id" class="brutal-input">
                        <option value="">Todos</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Estado</label>
                    <select name="status" class="brutal-input">
                        <option value="">Todos</option>
                        <option value="low_stock" @selected(request('status') === 'low_stock')>Stock Bajo</option>
                        <option value="out_of_stock" @selected(request('status') === 'out_of_stock')>Sin Stock</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="brutal-button">Filtrar</button>
                </div>
            </form>
        </div>

        {{-- Métricas --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="brutal-stat">
                <span class="brutal-label text-[10px] text-muted">Productos con Stock</span>
                <span class="font-mono text-2xl font-bold">{{ $with_stock }}</span>
            </div>
            <div class="brutal-stat">
                <span class="brutal-label text-[10px] text-muted">Stock Bajo</span>
                <span class="font-mono text-2xl font-bold text-accent">{{ $low_stock }}</span>
            </div>
            <div class="brutal-stat">
                <span class="brutal-label text-[10px] text-muted">Sin Stock</span>
                <span class="font-mono text-2xl font-bold text-danger">{{ $out_of_stock }}</span>
            </div>
            <div class="brutal-stat">
                <span class="brutal-label text-[10px] text-muted">Valor Total</span>
                <span class="font-mono text-2xl font-bold">${{ number_format($total_value, 2, ',', '.') }}</span>
            </div>
        </div>

        {{-- Tabla de inventario --}}
        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Almacén</th>
                        <th>Stock</th>
                        <th>Mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        @php
                            $isOut = $stock->quantity <= 0;
                            $isLow = ! $isOut && $stock->quantity <= $stock->minimum_stock_snapshot;
                        @endphp
                        <tr>
                            <td>{{ $stock->product?->name ?? '—' }}</td>
                            <td class="font-mono text-xs">{{ $stock->product?->sku ?? '—' }}</td>
                            <td>{{ $stock->warehouse?->name ?? '—' }}</td>
                            <td class="font-mono font-bold">{{ $stock->quantity }}</td>
                            <td class="font-mono">{{ $stock->minimum_stock_snapshot }}</td>
                            <td>
                                @if($isOut)
                                    <span class="brutal-badge bg-danger text-white">Sin Stock</span>
                                @elseif($isLow)
                                    <span class="brutal-badge bg-accent text-ink">Stock Bajo</span>
                                @else
                                    <span class="brutal-badge bg-success text-white">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-muted">
                                No hay datos de inventario para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
