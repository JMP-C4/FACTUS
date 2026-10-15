<x-app-layout>
    <x-slot name="header">
        <h2 class="brutal-kicker text-lg">Reporte de Ventas</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Fecha Inicio</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="brutal-input">
                </div>
                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Fecha Fin</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="brutal-input">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="brutal-button">Filtrar</button>
                </div>
            </form>
        </div>

        {{-- Métricas --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="brutal-stat">
                <span class="brutal-label text-[10px] text-muted">Total Ventas</span>
                <span class="font-mono text-2xl font-bold">${{ number_format($total, 2, ',', '.') }}</span>
            </div>
            <div class="brutal-stat">
                <span class="brutal-label text-[10px] text-muted">Facturas Emitidas</span>
                <span class="font-mono text-2xl font-bold">{{ $count }}</span>
            </div>
            <div class="brutal-stat">
                <span class="brutal-label text-[10px] text-muted">IVA Recaudado</span>
                <span class="font-mono text-2xl font-bold">${{ number_format($tax, 2, ',', '.') }}</span>
            </div>
            <div class="brutal-stat">
                <span class="brutal-label text-[10px] text-muted">Ticket Promedio</span>
                <span class="font-mono text-2xl font-bold">${{ number_format($average, 2, ',', '.') }}</span>
            </div>
        </div>

        {{-- Tabla de ventas --}}
        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Subtotal</th>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="text-xs">{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                            <td class="font-mono font-bold">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->customer?->display_name ?? 'Consumidor Final' }}</td>
                            <td class="font-mono">${{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
                            <td class="font-mono">${{ number_format($invoice->tax_total, 2, ',', '.') }}</td>
                            <td class="font-mono font-bold">${{ number_format($invoice->total, 2, ',', '.') }}</td>
                            <td>
                                <span class="brutal-badge bg-{{ $invoice->status->color() }} text-white">
                                    {{ $invoice->status->label() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-muted">
                                No hay datos de ventas para el período seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
