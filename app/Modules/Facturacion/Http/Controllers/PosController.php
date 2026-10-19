<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Clientes\Contracts\CustomerDataProviderContract;
use App\Modules\Facturacion\Http\Requests\StoreInvoiceRequest;
use App\Modules\Facturacion\Services\InvoiceService;
use App\Modules\Productos\Contracts\ProductCatalogContract;
use App\Modules\Productos\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        $companyId = $this->currentCompanyId($request);

        if ($companyId === null) {
            return response()->json([
                'message' => 'Debes pertenecer a una compañía para registrar ventas.',
            ], 403);
        }

        $invoice = DB::transaction(function () use ($request, $companyId) {
            $invoice = $this->invoiceService->create(
                $companyId,
                $request->user()->id,
                $request->validated(),
            );

            return $this->invoiceService->issue($invoice);
        });

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

    /**
     * Autoriza el retiro de un producto del carrito del POS.
     *
     * Admite dos métodos: un código de autorización configurado o las
     * credenciales de un supervisor/admin.
     */
    public function authorizeRemoval(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ]);

        $code = (string) config('pos.authorization_code');

        if (($validated['code'] ?? '') !== '' && $code !== '' && hash_equals($code, (string) $validated['code'])) {
            return response()->json([
                'authorized' => true,
                'method' => 'code',
                'authorized_by' => 'Código de autorización',
            ]);
        }

        if (($validated['email'] ?? '') !== '' && ($validated['password'] ?? '') !== '') {
            $supervisor = User::query()->where('email', $validated['email'])->first();

            if (
                $supervisor !== null
                && Hash::check((string) $validated['password'], $supervisor->password)
                && $this->isRemovalSupervisor($supervisor)
            ) {
                return response()->json([
                    'authorized' => true,
                    'method' => 'credentials',
                    'authorized_by' => $supervisor->name,
                ]);
            }
        }

        return response()->json([
            'authorized' => false,
            'message' => 'Autorización denegada. Verifica el código o las credenciales de supervisor.',
        ], 403);
    }

    /**
     * Determina si un usuario puede autorizar el retiro de productos.
     */
    private function isRemovalSupervisor(User $user): bool
    {
        return $user->hasAnyRole(config('billing.pos_removal_roles'));
    }
}
