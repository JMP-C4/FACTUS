<?php

declare(strict_types=1);

namespace App\Modules\Inventario\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventario\Http\Requests\StoreWarehouseRequest;
use App\Modules\Inventario\Http\Requests\UpdateWarehouseRequest;
use App\Modules\Inventario\Models\Warehouse;
use App\Modules\Inventario\Services\WarehouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseService $warehouses,
    ) {}

    public function index(Request $request): View
    {
        $warehouses = $this->warehouses->list(
            $this->currentCompanyId($request),
            ['search' => $request->input('search')],
        );

        return view('inventario::warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('inventario::warehouses.create');
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);

        if ($companyId === null) {
            return back()->with('error', 'Debes pertenecer a una compañía para crear almacenes.');
        }

        $this->warehouses->create($companyId, $request->validated());

        return redirect()
            ->route('inventario.almacenes.index')
            ->with('success', 'Almacén creado exitosamente.');
    }

    public function edit(Request $request, Warehouse $warehouse): View
    {
        $this->authorizeWarehouse($warehouse, $request);

        return view('inventario::warehouses.edit', compact('warehouse'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorizeWarehouse($warehouse, $request);

        $this->warehouses->update($warehouse, $request->validated());

        return redirect()
            ->route('inventario.almacenes.index')
            ->with('success', 'Almacén actualizado exitosamente.');
    }

    public function setDefault(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorizeWarehouse($warehouse, $request);

        $this->warehouses->setDefault($warehouse);

        return redirect()
            ->route('inventario.almacenes.index')
            ->with('success', 'Almacén marcado como predeterminado.');
    }

    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorizeWarehouse($warehouse, $request);

        if ($this->warehouses->hasActivity($warehouse)) {
            return back()->with('error', 'No se puede eliminar un almacén con stock o movimientos registrados.');
        }

        $this->warehouses->delete($warehouse);

        return redirect()
            ->route('inventario.almacenes.index')
            ->with('success', 'Almacén eliminado exitosamente.');
    }

    private function authorizeWarehouse(Warehouse $warehouse, Request $request): void
    {
        $companyId = $request->user()->company_id;

        abort_if(
            $companyId !== null && $warehouse->company_id !== $companyId,
            403,
            'No tienes acceso a este almacén.',
        );
    }
}
