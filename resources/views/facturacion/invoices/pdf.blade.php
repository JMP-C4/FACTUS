<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Factura {{ $invoice->invoice_number }}</title>
        <style>
            * { box-sizing: border-box; }
            body {
                font-family: DejaVu Sans, Arial, sans-serif;
                font-size: 11px;
                color: #1a1a1a;
                margin: 0;
                padding: 28px 32px;
            }
            .header { width: 100%; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; margin-bottom: 18px; }
            .header td { vertical-align: top; }
            .company-name { font-size: 18px; font-weight: bold; margin: 0 0 4px; }
            .muted { color: #666; }
            .doc-title { text-align: right; }
            .doc-title .number { font-size: 16px; font-weight: bold; }
            .badge {
                display: inline-block;
                padding: 2px 8px;
                border: 1px solid #1a1a1a;
                border-radius: 3px;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #555; margin: 0 0 6px; }
            .info-table { width: 100%; margin-bottom: 18px; }
            .info-table td { padding: 2px 0; vertical-align: top; }
            .info-table .label { color: #666; width: 90px; }
            table.items { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
            table.items th {
                text-align: left;
                background: #f0f0f0;
                border-bottom: 2px solid #1a1a1a;
                padding: 6px 8px;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            table.items td { padding: 6px 8px; border-bottom: 1px solid #ddd; }
            .right { text-align: right; }
            .totals { width: 45%; margin-left: 55%; }
            .totals td { padding: 3px 0; }
            .totals tr.grand td { border-top: 2px solid #1a1a1a; padding-top: 8px; font-size: 14px; font-weight: bold; }
            .footer { margin-top: 24px; border-top: 2px solid #1a1a1a; padding-top: 10px; font-size: 9px; color: #666; }
        </style>
    </head>
    <body>
        <table class="header">
            <tr>
                <td style="width: 60%;">
                    <p class="company-name">{{ $invoice->company?->name ?? config('app.name') }}</p>
                    @if($invoice->company?->nit)
                        <div class="muted">NIT: {{ $invoice->company->nit }}</div>
                    @endif
                    @if($invoice->company?->address)
                        <div class="muted">{{ $invoice->company->address }}@if($invoice->company?->city), {{ $invoice->company->city }}@endif</div>
                    @endif
                    @if($invoice->company?->phone || $invoice->company?->email)
                        <div class="muted">
                            {{ $invoice->company?->phone }}
                            @if($invoice->company?->phone && $invoice->company?->email) · @endif
                            {{ $invoice->company?->email }}
                        </div>
                    @endif
                    @if($invoice->branch?->name)
                        <div class="muted">Sucursal: {{ $invoice->branch->name }}</div>
                    @endif
                </td>
                <td class="doc-title" style="width: 40%;">
                    <div class="section-title">Factura de venta</div>
                    <div class="number">{{ $invoice->invoice_number }}</div>
                    <div style="margin-top: 6px;">
                        <span class="badge">{{ $invoice->status->label() }}</span>
                        @if($invoice->status_dian)
                            <span class="badge">{{ $invoice->status_dian }}</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <div class="section-title">Cliente</div>
                    <div><strong>{{ $invoice->customer?->display_name ?? 'Consumidor Final' }}</strong></div>
                    @if($invoice->customer?->document_number)
                        <div class="muted">Documento: {{ $invoice->customer->document_number }}</div>
                    @endif
                </td>
                <td style="width: 50%;">
                    <div class="section-title">Datos del documento</div>
                    <div>Emitida: {{ ($invoice->issued_at ?? $invoice->created_at)?->format('d/m/Y H:i') }}</div>
                    @if($invoice->numberingRange?->prefix)
                        <div class="muted">Prefijo: {{ $invoice->numberingRange->prefix }}</div>
                    @endif
                    @if($invoice->numberingRange?->resolution_number)
                        <div class="muted">Resolución: {{ $invoice->numberingRange->resolution_number }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="right">Cant.</th>
                    <th class="right">Precio unit.</th>
                    <th class="right">Impuesto</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="right">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                        <td class="right">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                        <td class="right">${{ number_format($item->tax_total, 2, ',', '.') }}</td>
                        <td class="right">${{ number_format($item->line_total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="muted">Subtotal</td>
                <td class="right">${{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
            </tr>
            @if($invoice->discount_total > 0)
                <tr>
                    <td class="muted">Descuento</td>
                    <td class="right">-${{ number_format($invoice->discount_total, 2, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td class="muted">Impuestos</td>
                <td class="right">${{ number_format($invoice->tax_total, 2, ',', '.') }}</td>
            </tr>
            <tr class="grand">
                <td>Total</td>
                <td class="right">${{ number_format($invoice->total, 2, ',', '.') }}</td>
            </tr>
        </table>

        @if($invoice->payments->isNotEmpty())
            <div class="section-title" style="margin-top: 18px;">Pagos</div>
            <table class="items">
                <thead>
                    <tr>
                        <th>Método</th>
                        <th class="right">Monto</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_method_code }}</td>
                            <td class="right">${{ number_format($payment->amount, 2, ',', '.') }}</td>
                            <td class="muted">
                                @if($payment->payment_method_code === 'cash')
                                    Recibido: ${{ number_format($payment->cash_received, 2, ',', '.') }}
                                    · Cambio: ${{ number_format($payment->cash_change, 2, ',', '.') }}
                                @elseif(in_array($payment->payment_method_code, ['credit_card', 'debit_card']))
                                    Tarjeta: ****{{ $payment->card_last_four }}
                                    · Aprobación: {{ $payment->card_approval_code }}
                                @elseif($payment->payment_method_code === 'transfer')
                                    Ref: {{ $payment->reference_number }} · Banco: {{ $payment->bank_entity }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif

        @if($invoice->cufe || $invoice->qr_url)
            <table style="width: 100%; margin-top: 18px; border-top: 2px solid #1a1a1a; padding-top: 10px;">
                <tr>
                    <td style="width: 190px; vertical-align: middle;">
                        <x-document-codes :invoice="$invoice" :qr-size="104" :barcode-height="42" />
                    </td>
                    <td style="vertical-align: middle; padding-left: 10px;">
                        <div class="section-title">Verificación electrónica</div>
                        @if($invoice->cufe)
                            <div style="font-size: 9px; color: #666; word-break: break-all;">
                                <strong>CUFE:</strong> {{ $invoice->cufe }}
                            </div>
                        @endif
                        @if($invoice->qr_url)
                            <div style="font-size: 9px; color: #666; word-break: break-all; margin-top: 4px;">
                                <strong>Enlace:</strong> {{ $invoice->qr_url }}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        @endif

        <div class="footer">
            Documento generado el {{ now()->format('d/m/Y H:i') }}. Entorno local: esta representación
            no corresponde a un documento válido ante la DIAN.
        </div>
    </body>
</html>
