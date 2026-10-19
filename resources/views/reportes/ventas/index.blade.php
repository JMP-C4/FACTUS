<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="brutal-kicker text-lg">Reporte de Ventas</h2>
            @if(auth()->user()->hasPermission('reportes.exportar'))
                <a href="{{ route('reportes.ventas.export', request()->query()) }}" class="brutal-button">
                    Exportar CSV
                </a>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Filtros --}}
        <div class="brutal-card mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Fecha Inicio</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="brutal-input">
                </div>
                <div class="min-w-[150px]">
                    <label class="brutal-label mb-1 block">Fecha Fin</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="brutal-input">
                </div>
                <div class="min-w-[180px]">
                    <label class="brutal-label mb-1 block">Cajero</label>
                    <select name="cashier_id" class="brutal-input">
                        <option value="">Todos</option>
                        @foreach($cashiers as $cashier)
                            <option value="{{ $cashier->id }}" {{ (string) request('cashier_id') === (string) $cashier->id ? 'selected' : '' }}>
                                {{ $cashier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[180px]">
                    <label class="brutal-label mb-1 block">Método de pago</label>
                    <select name="payment_method" class="brutal-input">
                        <option value="">Todos</option>
                        @foreach($paymentMethods as $code => $label)
                            <option value="{{ $code }}" {{ request('payment_method') === $code ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="brutal-button">Filtrar</button>
                </div>
            </form>
        </div>

        @php
            $formatMoney = static fn (float|int $value): string => '$'.number_format((float) $value, 2, ',', '.');

            $trendFor = static function (float|int $current, float|int|null $previous): ?array {
                if ($previous === null) {
                    return null;
                }

                $previous = (float) $previous;

                if (abs($previous) < 0.0001) {
                    return $current > 0
                        ? ['direction' => 'up', 'percent' => 100.0]
                        : ['direction' => 'flat', 'percent' => 0.0];
                }

                $percent = ((($current - $previous) / abs($previous)) * 100);

                return [
                    'direction' => abs($percent) < 0.05 ? 'flat' : ($percent > 0 ? 'up' : 'down'),
                    'percent' => $percent,
                ];
            };

            $metrics = [
                ['label' => 'Venta Bruta', 'value' => $formatMoney($total), 'current' => $total, 'previous' => $previous['total'] ?? null, 'good' => 'up', 'tone' => ''],
                ['label' => 'Facturas Emitidas', 'value' => $count, 'current' => $count, 'previous' => $previous['count'] ?? null, 'good' => 'up', 'tone' => ''],
                ['label' => 'IVA Recaudado', 'value' => $formatMoney($tax), 'current' => $tax, 'previous' => $previous['tax'] ?? null, 'good' => 'up', 'tone' => ''],
                ['label' => 'Ticket Promedio', 'value' => $formatMoney($average), 'current' => $average, 'previous' => $previous['average'] ?? null, 'good' => 'up', 'tone' => ''],
                ['label' => 'Notas de Crédito', 'value' => $formatMoney($credit_notes), 'current' => $credit_notes, 'previous' => $previous['credit_notes'] ?? null, 'good' => 'down', 'tone' => 'text-danger'],
                ['label' => 'Venta Neta', 'value' => $formatMoney($net), 'current' => $net, 'previous' => $previous['net'] ?? null, 'good' => 'up', 'tone' => 'text-success'],
            ];

            $dailyMax = max(array_map(static fn (array $day): float => (float) $day['total'], $daily ?: [['total' => 0.0]]));
            $dailyMax = $dailyMax > 0 ? $dailyMax : 1.0;

            $methodColors = [
                'cash' => 'var(--color-primary)',
                'debit_card' => 'var(--color-accent)',
                'credit_card' => 'var(--color-danger)',
                'transfer' => 'var(--color-success)',
            ];

            $breakdown = collect($payment_breakdown)->filter(static fn ($amount): bool => (float) $amount > 0)->sortDesc();
            $breakdownTotal = (float) $breakdown->sum() ?: 1.0;
        @endphp

        {{-- Métricas con tendencia (ganancia / pérdida) --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-6">
            @foreach ($metrics as $metric)
                @php $trend = $trendFor($metric['current'], $metric['previous']); @endphp
                <div class="brutal-stat">
                    <div class="flex items-start justify-between gap-2">
                        <span class="brutal-label text-[10px] text-muted">{{ $metric['label'] }}</span>

                        @if ($trend)
                            @php
                                $isGood = ($trend['direction'] === 'up') === ($metric['good'] === 'up');
                                $arrowTone = $trend['direction'] === 'flat'
                                    ? 'text-muted'
                                    : ($isGood ? 'text-success' : 'text-danger');
                            @endphp
                            <span
                                class="inline-flex items-center gap-1 font-mono text-xs font-bold {{ $arrowTone }}"
                                title="vs. período anterior"
                            >
                                @if ($trend['direction'] === 'up')
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7" /></svg>
                                @elseif ($trend['direction'] === 'down')
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12l7 7 7-7" /></svg>
                                @else
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14" /></svg>
                                @endif
                                {{ number_format(abs($trend['percent']), 1, ',', '.') }}%
                            </span>
                        @endif
                    </div>

                    <span class="font-mono text-2xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</span>

                    @if ($metric['previous'] !== null)
                        <span class="brutal-label text-[9px] text-muted">
                            antes {{ $formatMoney($metric['previous']) }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Gráficas --}}
        <div class="grid gap-4 lg:grid-cols-[1.6fr_1fr] mb-6">
            {{-- Ventas por día --}}
            <div class="brutal-card">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="brutal-kicker text-sm">Ventas por día</h3>
                    <span class="brutal-label text-[10px] text-muted">{{ count($daily) }} día(s)</span>
                </div>

                @if (count($daily) > 0)
                    <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="h-40 w-full" role="img" aria-label="Ventas por día">
                        @php $slot = 100 / count($daily); @endphp
                        @foreach ($daily as $index => $day)
                            @php
                                $barHeight = max(((float) $day['total'] / $dailyMax) * 38, 0.4);
                                $isLast = $index === count($daily) - 1;
                            @endphp
                            <rect
                                x="{{ number_format($index * $slot + ($slot * 0.15), 4, '.', '') }}"
                                y="{{ number_format(40 - $barHeight, 4, '.', '') }}"
                                width="{{ number_format($slot * 0.7, 4, '.', '') }}"
                                height="{{ number_format($barHeight, 4, '.', '') }}"
                                fill="{{ $isLast ? 'var(--color-primary)' : 'var(--color-accent)' }}"
                            >
                                <title>{{ $day['date'] }}: {{ $formatMoney($day['total']) }} ({{ $day['count'] }} facturas)</title>
                            </rect>
                        @endforeach
                    </svg>
                @else
                    <div class="flex h-40 items-center justify-center text-muted">
                        Sin ventas en el período.
                    </div>
                @endif
            </div>

            {{-- Desglose por método de pago --}}
            <div class="brutal-card">
                <h3 class="brutal-kicker text-sm mb-4">Métodos de pago</h3>

                @if ($breakdown->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-6">
                        <svg viewBox="0 0 42 42" class="h-36 w-36 shrink-0" role="img" aria-label="Distribución por método de pago">
                            <circle cx="21" cy="21" r="15.915" fill="none" stroke="var(--color-surface-muted)" stroke-width="6" />
                            @php $offset = 25; @endphp
                            @foreach ($breakdown as $code => $amount)
                                @php
                                    $percent = ((float) $amount / $breakdownTotal) * 100;
                                    $color = $methodColors[$code] ?? 'var(--color-ink)';
                                @endphp
                                <circle
                                    cx="21" cy="21" r="15.915" fill="none"
                                    stroke="{{ $color }}" stroke-width="6"
                                    stroke-dasharray="{{ number_format($percent, 4, '.', '') }} {{ number_format(100 - $percent, 4, '.', '') }}"
                                    stroke-dashoffset="{{ number_format($offset, 4, '.', '') }}"
                                >
                                    <title>{{ $paymentMethods[$code] ?? $code }}</title>
                                </circle>
                                @php $offset -= $percent; @endphp
                            @endforeach
                        </svg>

                        <div class="min-w-[160px] flex-1 space-y-2">
                            @foreach ($breakdown as $code => $amount)
                                <div class="flex items-center justify-between gap-2 text-sm">
                                    <span class="flex items-center gap-2">
                                        <span class="inline-block h-3 w-3 border-2 border-ink" style="background-color: {{ $methodColors[$code] ?? 'var(--color-ink)' }}"></span>
                                        <span class="text-xs">{{ $paymentMethods[$code] ?? $code }}</span>
                                    </span>
                                    <span class="font-mono text-xs font-bold">{{ $formatMoney($amount) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="flex h-36 items-center justify-center text-center text-muted">
                        Sin pagos registrados en el período.
                    </div>
                @endif
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
                        <th>Cajero</th>
                        <th>Método de pago</th>
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
                            <td>{{ $invoice->user?->name ?? '—' }}</td>
                            <td class="text-xs">
                                {{ $invoice->payments->pluck('payment_method_code')->unique()->map(fn ($code) => $paymentMethods[$code] ?? $code)->implode(' + ') ?: '—' }}
                            </td>
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
                            <td colspan="9" class="text-center py-8 text-muted">
                                No hay datos de ventas para el período seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
