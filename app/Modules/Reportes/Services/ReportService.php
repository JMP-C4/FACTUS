<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Services;

use App\Models\User;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\CreditNote;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\StockMovement;
use App\Modules\Inventario\Models\Warehouse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ReportService
{
    /**
     * Días sin movimiento para considerar un producto de baja rotación.
     */
    public const LOW_ROTATION_DAYS = 30;

    /**
     * Etiquetas legibles de los métodos de pago soportados (catálogo central).
     *
     * @return array<string, string>
     */
    public static function paymentMethods(): array
    {
        return config('billing.payment_methods');
    }

    /**
     * Resumen de ventas por rango de fechas, cajero y método de pago.
     *
     * Incluye la serie diaria, el desglose por método de pago y, cuando hay
     * un rango completo, los totales del período inmediatamente anterior para
     * comparar tendencias (ganancia/pérdida).
     *
     * @return array{
     *     total: float,
     *     count: int,
     *     tax: float,
     *     average: float,
     *     credit_notes: float,
     *     net: float,
     *     invoices: Collection<int, Invoice>,
     *     daily: array<int, array{date: string, total: float, count: int}>,
     *     payment_breakdown: array<string, float>,
     *     previous: array{total: float, count: int, tax: float, average: float, credit_notes: float, net: float}|null
     * }
     */
    public function salesSummary(
        ?int $companyId,
        ?string $from = null,
        ?string $to = null,
        ?int $cashierId = null,
        ?string $paymentMethod = null,
    ): array {
        $query = Invoice::forCompany($companyId)
            ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::Validated->value])
            ->with(['customer', 'user', 'payments'])
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->when($cashierId, fn ($q) => $q->where('user_id', $cashierId))
            ->when($paymentMethod, fn ($q) => $q->whereHas('payments', fn ($p) => $p->where('payment_method_code', $paymentMethod)))
            ->latest();

        $invoices = $query->get();

        $total = (float) $invoices->sum('total');
        $tax = (float) $invoices->sum('tax_total');
        $count = $invoices->count();

        $creditNotes = (float) CreditNote::forCompany($companyId)
            ->applied()
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->sum('total');

        return [
            'total' => $total,
            'count' => $count,
            'tax' => $tax,
            'average' => $count > 0 ? $total / $count : 0.0,
            'credit_notes' => $creditNotes,
            'net' => $total - $creditNotes,
            'invoices' => $invoices,
            'daily' => $this->dailySeries($invoices),
            'payment_breakdown' => $this->paymentBreakdown($invoices),
            'previous' => $this->previousPeriod($companyId, $from, $to, $cashierId, $paymentMethod),
        ];
    }

    /**
     * Serie diaria de ventas para gráficas.
     *
     * @param  Collection<int, Invoice>  $invoices
     * @return array<int, array{date: string, total: float, count: int}>
     */
    private function dailySeries(Collection $invoices): array
    {
        return $invoices
            ->groupBy(fn (Invoice $invoice): string => $invoice->created_at->toDateString())
            ->sortKeys()
            ->map(fn (Collection $group, string $date): array => [
                'date' => $date,
                'total' => (float) $group->sum('total'),
                'count' => $group->count(),
            ])
            ->values()
            ->all();
    }

    /**
     * Desglose del monto total cobrado por método de pago.
     *
     * @param  Collection<int, Invoice>  $invoices
     * @return array<string, float>
     */
    private function paymentBreakdown(Collection $invoices): array
    {
        $breakdown = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice->payments as $payment) {
                $code = $payment->payment_method_code ?: $invoice->payment_method_code;

                if ($code === null) {
                    continue;
                }

                $breakdown[$code] = ($breakdown[$code] ?? 0.0) + (float) $payment->amount;
            }
        }

        return $breakdown;
    }

    /**
     * Totales del período anterior de igual longitud, para comparar tendencias.
     *
     * @return array{total: float, count: int, tax: float, average: float, credit_notes: float, net: float}|null
     */
    private function previousPeriod(
        ?int $companyId,
        ?string $from,
        ?string $to,
        ?int $cashierId,
        ?string $paymentMethod,
    ): ?array {
        if ($from === null || $to === null) {
            return null;
        }

        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        if ($start->gt($end)) {
            return null;
        }

        $days = (int) $start->diffInDays($end) + 1;
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();

        $invoices = Invoice::forCompany($companyId)
            ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::Validated->value])
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->when($cashierId, fn ($q) => $q->where('user_id', $cashierId))
            ->when($paymentMethod, fn ($q) => $q->whereHas('payments', fn ($p) => $p->where('payment_method_code', $paymentMethod)))
            ->get();

        $total = (float) $invoices->sum('total');
        $count = $invoices->count();

        $creditNotes = (float) CreditNote::forCompany($companyId)
            ->applied()
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('total');

        return [
            'total' => $total,
            'count' => $count,
            'tax' => (float) $invoices->sum('tax_total'),
            'average' => $count > 0 ? $total / $count : 0.0,
            'credit_notes' => $creditNotes,
            'net' => $total - $creditNotes,
        ];
    }

    /**
     * Cajeros con facturas registradas en la compañía.
     *
     * @return Collection<int, User>
     */
    public function cashiers(?int $companyId): Collection
    {
        return User::query()
            ->whereIn('id', Invoice::forCompany($companyId)->select('user_id'))
            ->orderBy('name')
            ->get();
    }

    /**
     * Etiqueta legible de un método de pago.
     */
    public function paymentLabel(string $code): string
    {
        return self::paymentMethods()[$code] ?? $code;
    }

    /**
     * Reporte de inventario por almacén, estado y categoría.
     *
     * @return array{
     *     with_stock: int,
     *     low_stock: int,
     *     out_of_stock: int,
     *     low_rotation: int,
     *     total_value: float,
     *     stocks: Collection<int, Stock>,
     *     warehouses: Collection<int, Warehouse>
     * }
     */
    public function inventoryReport(
        ?int $companyId,
        ?int $warehouseId = null,
        ?string $status = null,
        ?int $categoryId = null,
    ): array {
        $stocks = Stock::query()
            ->with(['product', 'warehouse'])
            ->when($companyId !== null, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('company_id', $companyId)))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($categoryId, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('category_id', $categoryId)))
            ->when($status === 'low_stock', fn ($q) => $q->whereColumn('quantity', '<=', 'minimum_stock_snapshot')->where('quantity', '>', 0))
            ->when($status === 'out_of_stock', fn ($q) => $q->where('quantity', '<=', 0))
            ->when($status === 'low_rotation', fn ($q) => $this->applyLowRotationFilter($q))
            ->get();

        $this->attachLastMovements($stocks, $companyId);

        $summary = $this->inventorySummary($companyId, $warehouseId, $categoryId);

        $totalValue = $stocks->sum(fn (Stock $stock): float => (float) ($stock->product?->price ?? 0) * $stock->quantity);

        return [
            'with_stock' => $summary['with_stock'],
            'low_stock' => $summary['low_stock'],
            'out_of_stock' => $summary['out_of_stock'],
            'low_rotation' => $summary['low_rotation'],
            'total_value' => (float) $totalValue,
            'stocks' => $stocks,
            'warehouses' => Warehouse::forCompany($companyId)->orderBy('name')->get(),
        ];
    }

    /**
     * Totales agregados del inventario (sin cargar todas las existencias en memoria).
     *
     * @return array{with_stock: int, low_stock: int, out_of_stock: int, low_rotation: int}
     */
    private function inventorySummary(?int $companyId, ?int $warehouseId, ?int $categoryId): array
    {
        $base = fn () => Stock::query()
            ->when($companyId !== null, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('company_id', $companyId)))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($categoryId, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('category_id', $categoryId)));

        return [
            'with_stock' => $base()->where('quantity', '>', 0)->count(),
            'low_stock' => $base()->where('quantity', '>', 0)
                ->whereColumn('quantity', '<=', 'minimum_stock_snapshot')
                ->count(),
            'out_of_stock' => $base()->where('quantity', '<=', 0)->count(),
            'low_rotation' => tap(
                $base(),
                fn ($query) => $this->applyLowRotationFilter($query),
            )->count(),
        ];
    }

    /**
     * Aplica el filtro de productos con existencias pero sin movimiento reciente.
     */
    private function applyLowRotationFilter($query): void
    {
        $cutoff = Carbon::now()->subDays(self::LOW_ROTATION_DAYS);

        $query->where('quantity', '>', 0)->whereNotExists(
            fn ($sub) => $sub->select(DB::raw(1))
                ->from('stock_movements')
                ->whereColumn('stock_movements.warehouse_id', 'stocks.warehouse_id')
                ->whereColumn('stock_movements.product_id', 'stocks.product_id')
                ->where('stock_movements.created_at', '>=', $cutoff)
        );
    }

    /**
     * Adjunta la fecha del último movimiento registrado a cada existencia.
     *
     * @param  Collection<int, Stock>  $stocks
     */
    private function attachLastMovements(Collection $stocks, ?int $companyId): void
    {
        if ($stocks->isEmpty()) {
            return;
        }

        $cutoff = Carbon::now()->subDays(self::LOW_ROTATION_DAYS);

        $lastMovements = StockMovement::query()
            ->when($companyId !== null, fn ($q) => $q->whereHas('warehouse', fn ($w) => $w->where('company_id', $companyId)))
            ->whereIn('warehouse_id', $stocks->pluck('warehouse_id')->unique()->all())
            ->select('warehouse_id', 'product_id', DB::raw('MAX(created_at) as last_at'))
            ->groupBy('warehouse_id', 'product_id')
            ->get()
            ->keyBy(fn (StockMovement $movement): string => $movement->warehouse_id.':'.$movement->product_id);

        foreach ($stocks as $stock) {
            $lastAt = $lastMovements->get($stock->warehouse_id.':'.$stock->product_id)?->last_at;

            $stock->setAttribute('last_movement_at', $lastAt ? Carbon::parse($lastAt) : null);
            $stock->setAttribute(
                'is_low_rotation',
                $stock->quantity > 0 && ($lastAt === null || Carbon::parse($lastAt)->lt(Carbon::now()->subDays(self::LOW_ROTATION_DAYS))),
            );
        }
    }
}
