<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Factura {{ $invoice->invoice_number }}</h2>
            <div class="flex items-center gap-2">
                @if($invoice->isDraft())
                    <form
                        method="POST"
                        action="{{ route('facturacion.facturas.issue', $invoice) }}"
                        class="inline"
                    >
                        @csrf
                        <button
                            type="submit"
                            class="brutal-button !bg-success !text-white"
                            onclick="return confirm('¿Emitir esta factura?')"
                        >
                            Emitir Factura
                        </button>
                    </form>
                @endif
                @if($invoice->status->isCancellable())
                    <form
                        method="POST"
                        action="{{ route('facturacion.facturas.cancel', $invoice) }}"
                        class="inline"
                    >
                        @csrf
                        <button
                            type="submit"
                            class="brutal-button !bg-danger !text-white"
                            onclick="return confirm('¿Anular esta factura?')"
                        >
                            Anular
                        </button>
                    </form>
                @endif
                <a href="{{ route('facturacion.facturas.index') }}" class="brutal-button">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="brutal-card space-y-6">
            {{-- Encabezado --}}
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-xl font-bold">{{ $invoice->invoice_number }}</h3>
                    <p class="mt-1 text-sm text-muted">
                        Rango: {{ $invoice->numberingRange?->prefix ?? '—' }} |
                        Resolución: {{ $invoice->numberingRange?->resolution_number ?? '—' }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="brutal-badge bg-{{ $invoice->status->color() }} text-{{ in_array($invoice->status->value, ['draft', 'pending']) ? 'ink' : 'white' }}">
                        {{ $invoice->status->label() }}
                    </span>
                    @if($invoice->status_dian)
                        <span class="brutal-badge bg-primary text-white mt-1">{{ $invoice->status_dian }}</span>
                    @endif
                </div>
            </div>

            {{-- Datos del cliente --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Cliente</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Nombre</span>
                        <p class="font-bold">{{ $invoice->customer?->display_name ?? 'Consumidor Final' }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Documento</span>
                        <p class="font-mono font-bold">{{ $invoice->customer?->document_number ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Ítems --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Ítems</h4>
                <div class="overflow-x-auto">
                    <table class="brutal-table">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th>Cant.</th>
                                <th>Precio Unit.</th>
                                <th>Impuesto</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="font-mono">{{ $item->quantity }}</td>
                                    <td class="font-mono">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                    <td class="font-mono">${{ number_format($item->tax_total, 2, ',', '.') }}</td>
                                    <td class="font-mono font-bold">${{ number_format($item->line_total, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Totales --}}
            <div class="border-t-[2px] border-ink pt-4">
                <div class="grid gap-2 sm:grid-cols-2 sm:justify-items-end">
                    <div class="w-full sm:w-64 space-y-2">
                        <div class="flex justify-between">
                            <span class="brutal-label text-xs">Subtotal</span>
                            <span class="font-mono">${{ number_format($invoice->subtotal, 2, ',', '.') }}</span>
                        </div>
                        @if($invoice->discount_total > 0)
                            <div class="flex justify-between text-danger">
                                <span class="brutal-label text-xs">Descuento</span>
                                <span class="font-mono">-${{ number_format($invoice->discount_total, 2, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="brutal-label text-xs">Impuestos</span>
                            <span class="font-mono">${{ number_format($invoice->tax_total, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t-[2px] border-ink pt-2">
                            <span class="brutal-kicker text-sm">Total</span>
                            <span class="font-mono text-lg font-bold">${{ number_format($invoice->total, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pagos --}}
            <div>
                <h4 class="brutal-kicker mb-3 text-xs">Pagos</h4>
                <div class="overflow-x-auto">
                    <table class="brutal-table">
                        <thead>
                            <tr>
                                <th>Método</th>
                                <th>Monto</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_method_code }}</td>
                                    <td class="font-mono font-bold">${{ number_format($payment->amount, 2, ',', '.') }}</td>
                                    <td class="text-sm text-muted">
                                        @if($payment->payment_method_code === 'cash')
                                            Recibido: ${{ number_format($payment->cash_received, 2, ',', '.') }}
                                            | Cambio: ${{ number_format($payment->cash_change, 2, ',', '.') }}
                                        @elseif($payment->payment_method_code === 'credit_card' || $payment->payment_method_code === 'debit_card')
                                            Tarjeta: ****{{ $payment->card_last_four }}
                                            | Aprobación: {{ $payment->card_approval_code }}
                                        @elseif($payment->payment_method_code === 'transfer')
                                            Ref: {{ $payment->reference_number }}
                                            | Banco: {{ $payment->bank_entity }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Datos de facturación local --}}
            @if($invoice->cufe || $invoice->qr_url)
                <div>
                    <h4 class="brutal-kicker mb-3 text-xs">Facturación local</h4>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @if($invoice->cufe)
                            <div>
                                <span class="brutal-label text-[10px] text-muted">CUFE</span>
                                <p class="font-mono text-xs break-all">{{ $invoice->cufe }}</p>
                            </div>
                        @endif
                        @if($invoice->qr_url)
                            <div>
                                <span class="brutal-label text-[10px] text-muted">Enlace de verificación</span>
                                <a href="{{ $invoice->qr_url }}" class="font-mono text-xs break-all underline text-primary" target="_blank" rel="noopener">
                                    {{ $invoice->qr_url }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Metadatos --}}
            <div class="pt-4 border-t-[2px] border-ink">
                <div class="grid gap-4 sm:grid-cols-3 text-xs text-muted">
                    <div>
                        <span class="brutal-label text-[10px]">Creada</span>
                        <p>{{ $invoice->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px]">Cajero</span>
                        <p>{{ $invoice->user?->name ?? '—' }}</p>
                    </div>
                    @if($invoice->issued_at)
                        <div>
                            <span class="brutal-label text-[10px]">Emitida</span>
                            <p>{{ $invoice->issued_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
