<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Notas de crédito</h2>
            <a href="{{ route('facturacion.facturas.index') }}" class="brutal-button">
                Ver facturas
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" action="{{ route('facturacion.notas.index') }}" class="flex flex-wrap gap-4">
                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Estado</label>
                    <select name="status" class="brutal-input">
                        <option value="">Todos</option>
                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Emitida</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="brutal-button">Filtrar</button>
                </div>
            </form>
        </div>

        {{-- Tabla de notas de crédito --}}
        <div class="brutal-card overflow-x-auto">
            <table class="brutal-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Factura origen</th>
                        <th>Motivo</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($creditNotes as $note)
                        <tr>
                            <td class="font-mono font-bold">{{ $note->number ?? '—' }}</td>
                            <td class="font-mono">{{ $note->invoice?->invoice_number ?? '—' }}</td>
                            <td class="text-sm">{{ $note->reason ?? '—' }}</td>
                            <td class="font-mono font-bold">${{ number_format($note->total, 2, ',', '.') }}</td>
                            <td>
                                <span class="brutal-badge bg-{{ $note->status->color() }} text-{{ in_array($note->status->value, ['draft', 'pending']) ? 'ink' : 'white' }}">
                                    {{ $note->status->label() }}
                                </span>
                            </td>
                            <td class="text-xs">{{ $note->issued_at?->format('d/m/Y H:i') ?? $note->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-right">
                                @if($note->invoice)
                                    <a
                                        href="{{ route('facturacion.facturas.show', $note->invoice) }}"
                                        class="brutal-button !px-2.5 !py-1.5"
                                        title="Ver factura"
                                        aria-label="Ver factura {{ $note->invoice->number ?? '' }}"
                                    >
                                        <x-action-icon name="view" />
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-muted">
                                No se encontraron notas de crédito.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="mt-6">
            {{ $creditNotes->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
