<?php

declare(strict_types=1);

namespace App\Modules\Inventario\Services;

use App\Modules\Inventario\Contracts\SaleStockContract;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\Warehouse;
use Illuminate\Support\Facades\DB;

final class SaleStockService implements SaleStockContract
{
    public function __construct(
        private readonly InventoryMovementGateway $movementGateway,
    ) {}

    public function assertAvailable(int $companyId, array $lines): void
    {
        $warehouse = $this->resolveWarehouse($companyId);

        if ($warehouse === null) {
            return;
        }

        $trackedLines = $this->trackedLines($warehouse->id, $lines);

        if ($trackedLines === []) {
            return;
        }

        $stocks = Stock::where('warehouse_id', $warehouse->id)
            ->whereIn('product_id', array_column($trackedLines, 'product_id'))
            ->with('product')
            ->get()
            ->keyBy('product_id');

        foreach ($trackedLines as $line) {
            $stock = $stocks->get($line['product_id']);

            if ($stock === null) {
                continue;
            }

            if (! $stock->hasSufficientStock($line['quantity'])) {
                abort(422, sprintf(
                    'Stock insuficiente para "%s". Disponible: %d, requerido: %d.',
                    $stock->product?->name ?? "producto #{$line['product_id']}",
                    $stock->quantity,
                    $line['quantity'],
                ));
            }
        }
    }

    public function deductForSale(
        int $companyId,
        array $lines,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $performedBy = null,
    ): void {
        $warehouse = $this->resolveWarehouse($companyId);

        if ($warehouse === null) {
            return;
        }

        $lines = $this->trackedLines($warehouse->id, $lines);

        if ($lines === []) {
            return;
        }

        DB::transaction(function () use ($warehouse, $lines, $referenceType, $referenceId, $performedBy): void {
            foreach ($lines as $line) {
                $this->movementGateway->exit(
                    $warehouse->id,
                    $line['product_id'],
                    $line['quantity'],
                    'Venta',
                    $performedBy,
                    $referenceType,
                    $referenceId,
                );
            }
        });
    }

    /**
     * Resuelve el almacén de la compañía: el marcado por defecto o, en su
     * defecto, el primero registrado. Devuelve null cuando la compañía aún no
     * tiene almacenes configurados (en ese caso no se gestiona stock).
     */
    private function resolveWarehouse(int $companyId): ?Warehouse
    {
        return Warehouse::forCompany($companyId)->default()->first()
            ?? Warehouse::forCompany($companyId)->orderBy('id')->first();
    }

    /**
     * Filtra las líneas con producto y con registro de stock (productos
     * rastreados). Los productos sin stock registrado no se descuentan para no
     * bloquear la venta de artículos no inventariables.
     *
     * @param  array<int, array{product_id: int|null, quantity: int}>  $lines
     * @return array<int, array{product_id: int, quantity: int}>
     */
    private function trackedLines(int $warehouseId, array $lines): array
    {
        $productIds = collect($lines)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($productIds === []) {
            return [];
        }

        $tracked = Stock::where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->all();

        return collect($lines)
            ->filter(fn (array $line): bool => $line['product_id'] !== null
                && in_array($line['product_id'], $tracked, true)
                && (int) $line['quantity'] > 0)
            ->map(fn (array $line): array => [
                'product_id' => (int) $line['product_id'],
                'quantity' => (int) $line['quantity'],
            ])
            ->values()
            ->all();
    }
}
