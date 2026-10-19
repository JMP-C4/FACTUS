@props([
    'invoice',
    'showBarcode' => true,
    'showQr' => true,
    'qrSize' => 120,
    'barcodeHeight' => 48,
])

@php
    $barcodeImage = $showBarcode
        ? app(\App\Modules\Facturacion\Services\BarcodeService::class)
            ->dataUri((string) ($invoice->invoice_number ?? ''))
        : null;

    $qrPayload = (string) ($invoice->qr_url ?: $invoice->cufe ?: '');
    $qrImage = $showQr
        ? app(\App\Modules\Facturacion\Services\QrCodeService::class)->dataUri($qrPayload)
        : null;
@endphp

@if($barcodeImage !== null || $qrImage !== null)
    <div {{ $attributes->merge(['class' => 'text-center']) }}>
        @if($qrImage !== null)
            <figure style="display:inline-block; vertical-align:top; margin:0 14px 8px 0; text-align:center;">
                <img
                    src="{{ $qrImage }}"
                    alt="Código QR de verificación de la factura {{ $invoice->invoice_number }}"
                    style="width: {{ $qrSize }}px; height: {{ $qrSize }}px;"
                >
                <figcaption style="font-size:9px; text-transform:uppercase; letter-spacing:0.14em; color:#666; margin-top:2px;">
                    Escanear para verificar
                </figcaption>
            </figure>
        @endif

        @if($barcodeImage !== null)
            <figure style="display:inline-block; vertical-align:top; margin:0 0 8px 0; text-align:center;">
                <img
                    src="{{ $barcodeImage }}"
                    alt="Código de barras de la factura {{ $invoice->invoice_number }}"
                    style="height: {{ $barcodeHeight }}px;"
                >
                <figcaption style="font-family: 'DejaVu Sans Mono', monospace; font-size:9px; letter-spacing:0.1em; margin-top:2px;">
                    {{ $invoice->invoice_number }}
                </figcaption>
            </figure>
        @endif
    </div>
@endif
