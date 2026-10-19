<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Productos\Contracts\CategoryRepositoryContract;
use App\Modules\Reportes\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportesController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly CategoryRepositoryContract $categories,
    ) {}

    public function ventas(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $dateFrom = $request->input('date_from') ?: now()->startOfMonth()->toDateString();
        $dateTo = $request->input('date_to') ?: now()->toDateString();

        $summary = $this->reports->salesSummary(
            $companyId,
            $dateFrom,
            $dateTo,
            $request->integer('cashier_id') ?: null,
            $request->input('payment_method'),
        );

        return view('reportes::ventas.index', array_merge($summary, [
            'cashiers' => $this->reports->cashiers($companyId),
            'paymentMethods' => ReportService::paymentMethods(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]));
    }

    public function inventario(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $report = $this->reports->inventoryReport(
            $companyId,
            $request->integer('warehouse_id') ?: null,
            $request->input('status'),
            $request->integer('category_id') ?: null,
        );

        return view('reportes::inventario.index', array_merge($report, [
            'categories' => $this->categories->getActive($companyId),
        ]));
    }

    public function exportVentas(Request $request): StreamedResponse
    {
        $summary = $this->reports->salesSummary(
            $request->user()->company_id,
            $request->input('date_from'),
            $request->input('date_to'),
            $request->integer('cashier_id') ?: null,
            $request->input('payment_method'),
        );

        $rows = $summary['invoices']->map(fn (Invoice $invoice): array => [
            $invoice->created_at->format('d/m/Y H:i'),
            $invoice->invoice_number,
            $invoice->customer?->display_name ?? 'Consumidor Final',
            $invoice->user?->name ?? '—',
            $invoice->payments->pluck('payment_method_code')->unique()
                ->map(fn (string $code): string => $this->reports->paymentLabel($code))
                ->implode(' + ') ?: $this->reports->paymentLabel($invoice->payment_method_code),
            number_format((float) $invoice->subtotal, 2, '.', ''),
            number_format((float) $invoice->tax_total, 2, '.', ''),
            number_format((float) $invoice->total, 2, '.', ''),
            $invoice->status->label(),
        ]);

        return $this->streamCsv(
            'reporte-ventas-'.now()->format('Ymd-His').'.csv',
            ['Fecha', 'Número', 'Cliente', 'Cajero', 'Método de pago', 'Subtotal', 'IVA', 'Total', 'Estado'],
            $rows,
        );
    }

    public function exportInventario(Request $request): StreamedResponse
    {
        $report = $this->reports->inventoryReport(
            $request->user()->company_id,
            $request->integer('warehouse_id') ?: null,
            $request->input('status'),
            $request->integer('category_id') ?: null,
        );

        $rows = $report['stocks']->map(fn ($stock): array => [
            $stock->warehouse?->name ?? '—',
            $stock->product?->name ?? '—',
            $stock->product?->sku ?? '—',
            $stock->quantity,
            $stock->minimum_stock_snapshot,
            number_format((float) ($stock->product?->price ?? 0), 2, '.', ''),
            number_format((float) ($stock->product?->price ?? 0) * $stock->quantity, 2, '.', ''),
            $stock->last_movement_at?->format('d/m/Y') ?? 'Sin movimientos',
        ]);

        return $this->streamCsv(
            'reporte-inventario-'.now()->format('Ymd-His').'.csv',
            ['Almacén', 'Producto', 'SKU', 'Cantidad', 'Stock mínimo', 'Precio', 'Valor', 'Último movimiento'],
            $rows,
        );
    }

    /**
     * Genera y transmite un archivo CSV (UTF-8 con BOM).
     */
    private function streamCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
