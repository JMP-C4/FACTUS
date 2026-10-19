<?php

namespace App\Modules\Automatizacion\Processes;

use App\Modules\Automatizacion\Automation\AutomationProcess;
use App\Modules\Automatizacion\Automation\AutomationResult;
use App\Modules\Inventario\Services\InventoryService;

/**
 * Revisa los productos por debajo del stock mínimo de la compañía.
 */
class LowStockAlertProcess extends AutomationProcess
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {}

    public function key(): string
    {
        return 'inventario.alertas_stock_minimo';
    }

    public function label(): string
    {
        return 'Revisar stock mínimo';
    }

    public function description(): string
    {
        return 'Genera el listado de productos por debajo del stock mínimo para priorizar compras o reposición.';
    }

    public function permission(): string
    {
        return 'inventario.view';
    }

    public function icon(): string
    {
        return 'M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z';
    }

    public function handle(int $companyId, int $userId): AutomationResult
    {
        $alerts = $this->inventoryService->getLowStockAlerts($companyId);

        $metrics = [
            'Productos en alerta' => $alerts->count(),
            'Almacenes afectados' => $alerts->pluck('warehouse_id')->unique()->count(),
        ];

        if ($alerts->isEmpty()) {
            return AutomationResult::success('Ningún producto está por debajo del stock mínimo.', $metrics);
        }

        return AutomationResult::success(
            "Se detectaron {$alerts->count()} productos por debajo del stock mínimo.",
            $metrics,
        );
    }
}
