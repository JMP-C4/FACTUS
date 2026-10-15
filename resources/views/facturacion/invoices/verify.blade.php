<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Verificación {{ $invoice->invoice_number }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700|inter-tight:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-bg text-ink antialiased">
        <div class="mx-auto max-w-3xl px-4 py-12">
            <div class="brutal-card space-y-6">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="brutal-kicker text-xs text-muted">Verificación de factura</p>
                        <h1 class="text-2xl font-bold">{{ $invoice->invoice_number }}</h1>
                        <p class="text-sm text-muted">{{ $invoice->company?->name ?? config('app.name') }}</p>
                    </div>
                    <span class="brutal-badge bg-{{ $invoice->status->color() }} text-white">
                        {{ $invoice->status->label() }}
                    </span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Cliente</span>
                        <p class="font-bold">{{ $invoice->customer?->display_name ?? 'Consumidor Final' }}</p>
                    </div>
                    <div>
                        <span class="brutal-label text-[10px] text-muted">Fecha de emisión</span>
                        <p>{{ ($invoice->issued_at ?? $invoice->created_at)?->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="brutal-label text-[10px] text-muted">CUFE</span>
                        <p class="font-mono text-xs break-all">{{ $invoice->cufe ?? '—' }}</p>
                    </div>
                </div>

                <div>
                    <h2 class="brutal-kicker mb-3 text-xs">Ítems</h2>
                    <div class="overflow-x-auto">
                        <table class="brutal-table">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Cant.</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td class="font-mono">{{ $item->quantity }}</td>
                                        <td class="font-mono">${{ number_format($item->line_total, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end border-t-[2px] border-ink pt-4">
                    <div class="text-right">
                        <span class="brutal-label text-xs block">Total</span>
                        <span class="font-mono text-2xl font-bold">${{ number_format($invoice->total, 2, ',', '.') }}</span>
                    </div>
                </div>

                <p class="text-xs text-muted border-t-[2px] border-ink pt-4">
                    Documento emitido en entorno local. Esta verificación es demostrativa y no
                    corresponde a un documento válido ante la DIAN.
                </p>
            </div>
        </div>
    </body>
</html>
