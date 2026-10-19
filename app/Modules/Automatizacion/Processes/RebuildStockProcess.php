<?php

namespace App\Modules\Automatizacion\Processes;

use App\Modules\Automatizacion\Automation\AutomationProcess;
use App\Modules\Automatizacion\Automation\AutomationResult;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\StockMovement;

/**
 * Reconstruye el stock sumando los movimientos y corrige diferencias.
 */
class RebuildStockProcess extends AutomationProcess
{
    public function key(): string
    {
        return 'inventario.recalcular_stock';
    }

    public function label(): string
    {
        return 'Recalcular stock desde movimientos';
    }

    public function description(): string
    {
        return 'Recalcula el stock de cada almacén sumando sus movimientos y corrige las diferencias encontradas.';
    }

    public function permission(): string
    {
        return 'inventario.ajuste';
    }

    public function icon(): string
    {
        return 'M3 7h18v13H3zM3 4h18v3H3zM10 11h4';
    }

    public function dangerous(): bool
    {
        return true;
    }

    public function handle(int $companyId, int $userId): AutomationResult
    {
        $checked = 0;
        $corrected = 0;

        Stock::query()
            ->whereHas('warehouse', fn ($query) => $query->where('company_id', $companyId))
            ->chunkById(200, function ($stocks) use (&$checked, &$corrected): void {
                foreach ($stocks as $stock) {
                    $checked++;

                    $expected = (int) StockMovement::query()
                        ->where('warehouse_id', $stock->warehouse_id)
                        ->where('product_id', $stock->product_id)
                        ->sum('quantity');

                    if ($expected !== (int) $stock->quantity) {
                        $stock->update(['quantity' => $expected]);
                        $corrected++;
                    }
                }
            });

        $metrics = [
            'Revisados' => $checked,
            'Corregidos' => $corrected,
        ];

        return AutomationResult::success(
            $corrected === 0
                ? 'El stock ya estaba consistente con los movimientos.'
                : "Se corrigieron {$corrected} registros de {$checked} revisados.",
            $metrics,
        );
    }
}
