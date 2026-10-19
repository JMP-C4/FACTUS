<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Modules\Auditoria\Models\ActivityLog;
use App\Modules\Clientes\Models\Customer;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Productos\Models\Product;
use Illuminate\Support\Carbon;

/**
 * Construye la información real que alimenta el dashboard según el alcance
 * del usuario autenticado (global para super admin, compañía para el resto).
 */
final class DashboardService
{
    public function build(User $user): array
    {
        $companyId = $user->company_id;

        return [
            'isSuperAdmin' => $user->isSuperAdmin(),
            'summary' => $companyId === null
                ? $this->globalSummary()
                : $this->companySummary($companyId),
            'modules' => $this->modules($user),
            'logRows' => $this->recentActivity($companyId),
        ];
    }

    /**
     * @return array<int, array{label: string, value: string, caption: string, tone: string}>
     */
    private function globalSummary(): array
    {
        $companyTotal = Company::count();
        $activeCompanies = Company::where('is_active', true)->count();
        $userTotal = User::count();
        $monthSales = $this->monthSalesTotal();

        return [
            [
                'label' => 'Compañías',
                'value' => $this->count($companyTotal),
                'caption' => $activeCompanies.' activas de '.$companyTotal.' registradas en la plataforma.',
                'tone' => 'amber',
            ],
            [
                'label' => 'Usuarios',
                'value' => $this->count($userTotal),
                'caption' => 'Cuentas con acceso a la plataforma multiempresa.',
                'tone' => 'blue',
            ],
            [
                'label' => 'Ventas del mes',
                'value' => $this->money($monthSales),
                'caption' => $this->invoiceCount(Carbon::now()->startOfMonth(), null).' facturas emitidas o validadas en el mes en curso.',
                'tone' => 'black',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: string, caption: string, tone: string}>
     */
    private function companySummary(int $companyId): array
    {
        $today = [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];

        $salesToday = (float) Invoice::forCompany($companyId)
            ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::Validated->value])
            ->whereBetween('created_at', $today)
            ->sum('total');

        return [
            [
                'label' => 'Productos activos',
                'value' => (string) $this->productCount($companyId),
                'caption' => 'Ítems disponibles en el catálogo para la venta.',
                'tone' => 'blue',
            ],
            [
                'label' => 'Ventas de hoy',
                'value' => $this->money($salesToday),
                'caption' => 'Total facturado hoy (emitidas y validadas).',
                'tone' => 'black',
            ],
            [
                'label' => 'Facturas del mes',
                'value' => (string) $this->invoiceCount(Carbon::now()->startOfMonth(), $companyId),
                'caption' => 'Documentos emitidos o validados durante el mes en curso.',
                'tone' => 'amber',
            ],
        ];
    }

    /**
     * @return array<int, array{id: string, title: string, description: string, metric: string, status: string, tone: string, url: string|null}>
     */
    private function modules(User $user): array
    {
        $companyId = $user->company_id;
        $modules = [];

        if ($user->hasPermission('empresas.view')) {
            $companyTotal = Company::count();
            $modules[] = $this->module(
                'companias',
                'Compañías',
                'Gobierna la separación multiempresa y el estado de cada organización.',
                $this->count($companyTotal).' registradas',
                Company::where('is_active', true)->count().' activas',
                'amber',
                route('empresas.index'),
            );
        }

        if ($user->hasPermission('users.view')) {
            $modules[] = $this->module(
                'usuarios',
                'Usuarios',
                'Administra altas, accesos y el estado de los operadores.',
                $this->count(User::count()).' cuentas',
                'gestionar',
                'blue',
                route('usuarios.index'),
            );
        }

        if ($user->hasPermission('facturacion.view')) {
            $modules[] = $this->module(
                'facturacion',
                'Facturación local',
                'Emite facturas sin servicios externos: numeración, CUFE y QR generados localmente.',
                $this->invoiceCount(Carbon::now()->startOfMonth(), $companyId).' este mes',
                'operativa',
                'green',
                route('facturacion.facturas.index'),
            );
        }

        if ($user->hasPermission('productos.view')) {
            $modules[] = $this->module(
                'productos',
                'Productos',
                'Gestiona el catálogo, impuestos y disponibilidad de los ítems.',
                $this->productCount($companyId).' activos',
                'catálogo',
                'green',
                route('productos.index'),
            );
        }

        if ($user->hasPermission('clientes.view')) {
            $modules[] = $this->module(
                'clientes',
                'Clientes',
                'Centraliza los terceros disponibles para la facturación.',
                $this->customerCount($companyId).' registrados',
                'disponibles',
                'blue',
                route('clientes.index'),
            );
        }

        if ($user->hasPermission('inventario.view')) {
            $modules[] = $this->module(
                'inventario',
                'Inventario',
                'Controla existencias por almacén y la trazabilidad de movimientos.',
                $this->lowStockCount($companyId).' bajo mínimo',
                'monitorear',
                'amber',
                route('inventario.movimientos.index'),
            );
        }

        if ($user->hasPermission('reportes.ventas')) {
            $modules[] = $this->module(
                'reportes',
                'Reportes',
                'Analiza ventas por cajero y método de pago, y exporta a CSV.',
                $this->money($this->monthSalesTotal($companyId)).' este mes',
                'exportable',
                'green',
                route('reportes.ventas.index'),
            );
        }

        if ($user->hasPermission('platform.logs.view')) {
            $modules[] = $this->module(
                'logs',
                'Auditoría',
                'Bitácora de accesos, cambios y emisiones registradas por el sistema.',
                $this->activityTodayCount($companyId).' eventos hoy',
                'al día',
                'blue',
                route('auditoria.index'),
            );
        }

        if ($user->hasPermission('automatizacion.view')) {
            $modules[] = $this->module(
                'automatizacion',
                'Automatización',
                'Ejecuta procesos internos de mantenimiento y sincronización bajo demanda.',
                'disponible',
                'manual',
                'amber',
                route('automatizacion.index'),
            );
        }

        if ($user->isSuperAdmin()) {
            $modules[] = $this->module(
                'diagrama-er',
                'Diagrama E-R',
                'Explora las entidades de la base de datos, relaciones de claves foráneas y registros en tiempo real.',
                'esquema activo',
                'super admin',
                'blue',
                route('diagrama-er.index'),
            );
        }

        return $modules;
    }

    /**
     * @return array<int, array{event: string, owner: string, context: string, status: string}>
     */
    private function recentActivity(?int $companyId): array
    {
        $labels = [
            'login' => 'Inicio de sesión',
            'logout' => 'Cierre de sesión',
            'login_failed' => 'Acceso fallido',
            'invoice_issued' => 'Factura emitida',
            'invoice_cancelled' => 'Factura cancelada',
            'invoice_issue_failed' => 'Error de emisión',
            'credit_note_issued' => 'Nota de crédito emitida',
            'credit_note_issue_failed' => 'Error en nota de crédito',
        ];

        return ActivityLog::query()
            ->with('user')
            ->when($companyId !== null, fn ($query) => $query->whereHas('user', fn ($q) => $q->where('company_id', $companyId)))
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (ActivityLog $log): array => [
                'event' => $labels[$log->event] ?? ucfirst(str_replace('_', ' ', $log->event)),
                'owner' => $log->user?->name ?? 'Sistema',
                'context' => ucfirst($log->module),
                'status' => str_contains($log->event, 'failed') || str_contains($log->event, 'error')
                    ? 'Failed'
                    : 'Success',
            ])
            ->all();
    }

    /**
     * @return array{id: string, title: string, description: string, metric: string, status: string, tone: string, url: string|null}
     */
    private function module(
        string $id,
        string $title,
        string $description,
        string $metric,
        string $status,
        string $tone,
        ?string $url = null,
    ): array {
        return compact('id', 'title', 'description', 'metric', 'status', 'tone', 'url');
    }

    private function invoiceCount(Carbon $from, ?int $companyId): int
    {
        return Invoice::forCompany($companyId)
            ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::Validated->value])
            ->where('created_at', '>=', $from)
            ->count();
    }

    private function monthSalesTotal(?int $companyId = null): float
    {
        return (float) Invoice::forCompany($companyId)
            ->whereIn('status', [InvoiceStatus::Issued->value, InvoiceStatus::Validated->value])
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('total');
    }

    private function lowStockCount(?int $companyId): int
    {
        return Stock::query()
            ->when($companyId !== null, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('company_id', $companyId)))
            ->whereColumn('quantity', '<=', 'minimum_stock_snapshot')
            ->where('quantity', '>', 0)
            ->count();
    }

    private function activityTodayCount(?int $companyId): int
    {
        return ActivityLog::query()
            ->when($companyId !== null, fn ($query) => $query->whereHas('user', fn ($q) => $q->where('company_id', $companyId)))
            ->where('created_at', '>=', Carbon::today()->startOfDay())
            ->count();
    }

    private function productCount(?int $companyId): int
    {
        return Product::query()
            ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
            ->active()
            ->count();
    }

    private function customerCount(?int $companyId): int
    {
        return Customer::forCompany($companyId)->count();
    }

    private function money(float $value): string
    {
        if ($value >= 1_000_000) {
            return '$'.number_format($value / 1_000_000, 2, ',', '.').' M';
        }

        if ($value >= 1_000) {
            return '$'.number_format($value / 1_000, 0, ',', '.').' K';
        }

        return '$'.number_format($value, 0, ',', '.');
    }

    private function count(int $value): string
    {
        return str_pad((string) $value, 2, '0', STR_PAD_LEFT);
    }
}
