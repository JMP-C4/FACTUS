<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Services;

use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\Warehouse;
use Illuminate\Support\Collection;

final class ReportService
{
    /**
     * Resumen de ventas por rango de fechas.
     *
     * @return array{total: float, count: int, tax: float, average: float, invoices: Collection<int, Invoice>}
     */
    public function salesSummary(int $companyId, ?string $from = null, ?string $to = null): array
    {
        $query = Invoice::forCompany($companyId)
            ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::Validated->value])
            ->with(['customer'])
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->latest();

        $invoices = $query->get();

        $total = (float) $invoices->sum('total');
        $tax = (float) $invoices->sum('tax_total');
        $count = $invoices->count();

        return [
            'total' => $total,
            'count' => $count,
            'tax' => $tax,
            'average' => $count > 0 ? $total / $count : 0.0,
            'invoices' => $invoices,
        ];
    }

    /**
     * Reporte de inventario por almacén y estado.
     *
     * @return array{
     *     with_stock: int,
     *     low_stock: int,
     *     out_of_stock: int,
     *     total_value: float,
     *     stocks: Collection<int, Stock>,
     *     warehouses: Collection<int, Warehouse>
     * }
     */
    public function inventoryReport(int $companyId, ?int $warehouseId = null, ?string $status = null): array
    {
        $stocks = Stock::query()
            ->with(['product', 'warehouse'])
            ->whereHas('product', fn ($q) => $q->where('company_id', $companyId))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($status === 'low_stock', fn ($q) => $q->whereColumn('quantity', '<=', 'minimum_stock_snapshot')->where('quantity', '>', 0))
            ->when($status === 'out_of_stock', fn ($q) => $q->where('quantity', '<=', 0))
            ->get();

        $all = Stock::query()
            ->whereHas('product', fn ($q) => $q->where('company_id', $companyId))
            ->get();

        $totalValue = $stocks->sum(fn (Stock $stock): float => (float) ($stock->product?->price ?? 0) * $stock->quantity);

        return [
            'with_stock' => $all->where('quantity', '>', 0)->count(),
            'low_stock' => $all->filter(fn (Stock $s): bool => $s->quantity > 0 && $s->quantity <= $s->minimum_stock_snapshot)->count(),
            'out_of_stock' => $all->where('quantity', '<=', 0)->count(),
            'total_value' => (float) $totalValue,
            'stocks' => $stocks,
            'warehouses' => Warehouse::forCompany($companyId)->orderBy('name')->get(),
        ];
    }
}
