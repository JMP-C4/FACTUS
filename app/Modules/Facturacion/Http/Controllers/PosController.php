<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clientes\Contracts\CustomerDataProviderContract;
use App\Modules\Facturacion\Http\Requests\StoreInvoiceRequest;
use App\Modules\Facturacion\Services\InvoiceService;
use App\Modules\Productos\Contracts\ProductCatalogContract;
use App\Modules\Productos\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PosController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly ProductCatalogContract $catalog,
        private readonly CustomerDataProviderContract $customers,
    ) {}

    public function index(): View
    {
        return view('facturacion::pos');
    }

    /**
     * Búsqueda rápida de productos para el POS.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $request->validate([
            'term' => ['required', 'string', 'min:2'],
        ]);

        $companyId = $request->user()->company_id;

        $products = $this->catalog
            ->search($companyId, $request->input('term'))
            ->take(20)
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => (float) $product->price,
                'tax_rate' => (float) $product->tax_rate,
                'unit_measure_code' => $product->unit_measure_code,
            ])
            ->values();

        return response()->json($products);
    }

    /**
     * Búsqueda rápida de clientes para el POS.
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $request->validate([
            'term' => ['required', 'string', 'min:2'],
        ]);

        $companyId = $request->user()->company_id;

        $customers = $this->customers
            ->search($companyId, $request->input('term'))
            ->take(20)
            ->map(fn ($customer) => [
                'id' => $customer->id,
                'display_name' => $customer->display_name,
                'document_number' => $customer->document_number,
                'is_facturable' => (bool) $customer->is_facturable,
            ])
            ->values();

        return response()->json($customers);
    }

    /**
     * Procesa el checkout del POS: crea y emite la factura.
     */
    public function checkout(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoiceService->create(
            $request->user()->company_id,
            $request->user()->id,
            $request->validated(),
        );

        $invoice = $this->invoiceService->issue($invoice);

        return response()->json([
            'message' => 'Venta registrada y factura emitida.',
            'invoice' => [
                'id' => $invoice->id,
                'number' => $invoice->invoice_number,
                'total' => (float) $invoice->total,
                'status' => $invoice->status->value,
                'url' => $request->user()->hasPermission('facturacion.view')
                    ? route('facturacion.facturas.show', $invoice)
                    : null,
            ],
        ], 201);
    }
}
