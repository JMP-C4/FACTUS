<?php

namespace App\Modules\Inventario\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventario\Http\Requests\StoreStockAdjustmentRequest;
use App\Modules\Inventario\Http\Requests\StoreStockEntryRequest;
use App\Modules\Inventario\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {}

    /**
     * Listado de movimientos de inventario.
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $movements = $this->inventoryService->listMovements($companyId, [
            'warehouse_id' => $request->input('warehouse_id'),
            'movement_type' => $request->input('movement_type'),
            'product_id' => $request->input('product_id'),
        ]);

        $warehouses = $this->inventoryService->getWarehouses($companyId);

        return view('inventario::movements.index', compact('movements', 'warehouses'));
    }

    /**
     * Formulario de entrada de stock.
     */
    public function createEntry(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $warehouses = $this->inventoryService->getWarehouses($companyId);

        return view('inventario::movements.create-entry', compact('warehouses'));
    }

    /**
     * Registra una entrada de stock.
     */
    public function storeEntry(StoreStockEntryRequest $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);

        if ($companyId === null) {
            return back()->with('error', 'Debes pertenecer a una compañía para registrar entradas de stock.');
        }

        $this->inventoryService->registerEntry(
            companyId: $companyId,
            warehouseId: $request->input('warehouse_id'),
            productId: $request->input('product_id'),
            quantity: $request->input('quantity'),
            reason: $request->input('reason'),
            userId: $request->user()->id,
        );

        return redirect()
            ->route('inventario.movimientos.index')
            ->with('success', 'Entrada de stock registrada exitosamente.');
    }

    /**
     * Formulario de ajuste de stock.
     */
    public function createAdjustment(Request $request): View
    {
        $companyId = $request->user()->company_id;
        $warehouses = $this->inventoryService->getWarehouses($companyId);

        return view('inventario::movements.create-adjustment', compact('warehouses'));
    }

    /**
     * Registra un ajuste de stock.
     */
    public function storeAdjustment(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);

        if ($companyId === null) {
            return back()->with('error', 'Debes pertenecer a una compañía para registrar ajustes de stock.');
        }

        $this->inventoryService->registerAdjustment(
            companyId: $companyId,
            warehouseId: $request->input('warehouse_id'),
            productId: $request->input('product_id'),
            newQuantity: $request->input('new_quantity'),
            reason: $request->input('reason'),
            userId: $request->user()->id,
        );

        return redirect()
            ->route('inventario.movimientos.index')
            ->with('success', 'Ajuste de stock registrado exitosamente.');
    }
}
