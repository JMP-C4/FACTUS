<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Facturas</h2>
            @if(auth()->user()->hasPermission('facturacion.emitir'))
                <a href="{{ route('facturacion.facturas.create') }}" class="brutal-button !bg-primary !text-white">
                    Nueva Factura
                </a>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('facturacion.facturas.index') }}" class="flex flex-wrap gap-4">
                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Estado</label>
                    <select name="status" class="brutal-input">
                        <option value="">Todos</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Emitida</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="brutal-button">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabla de facturas --}}
        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>DIAN</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="font-mono font-bold">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->customer?->display_name ?? 'Consumidor Final' }}</td>
                            <td class="text-xs">{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                            <td class="font-mono font-bold">${{ number_format($invoice->total, 2, ',', '.') }}</td>
                            <td>
                                <span class="brutal-badge bg-{{ $invoice->status->color() }} text-{{ in_array($invoice->status->value, ['draft', 'pending']) ? 'ink' : 'white' }}">
                                    {{ $invoice->status->label() }}
                                </span>
                            </td>
                            <td>
                                @if($invoice->status_dian)
                                    <span class="brutal-badge bg-primary text-white">{{ $invoice->status_dian }}</span>
                                @else
                                    <span class="brutal-badge bg-muted text-white">Pendiente</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('facturacion.facturas.show', $invoice) }}"
                                        class="brutal-button !px-3 !py-1.5 !text-[10px]"
                                    >
                                        Ver
                                    </a>
                                    @if($invoice->isDraft())
                                        <form
                                            method="POST"
                                            action="{{ route('facturacion.facturas.issue', $invoice) }}"
                                            class="inline"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="brutal-button !px-3 !py-1.5 !text-[10px] !bg-success !text-white"
                                                onclick="return confirm('¿Emitir esta factura?')"
                                            >
                                                Emitir
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-muted">
                                No se encontraron facturas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-6">
            {{ $invoices->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
