<?php

declare(strict_types=1);

namespace Tests\Feature\Ui;

use App\Models\Company;
use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use Tests\Support\HasRoles;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use HasRoles;

    public function test_super_admin_dashboard_shows_global_metrics(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Compañías')
            ->assertSee('Usuarios')
            ->assertSee('Ventas del mes');
    }

    public function test_company_dashboard_shows_operational_metrics(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $admin = $this->createAdminUser($company, $branch);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Productos activos')
            ->assertSee('Ventas de hoy')
            ->assertSee('Facturas del mes');
    }

    public function test_dashboard_lists_real_recent_activity(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $admin = $this->createAdminUser($company, $branch);

        $this->actingAs($admin);
        app(AuditLoggerContract::class)->log(
            'invoice_issued',
            'facturacion',
            'Factura FAC-1 emitida.',
        );

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Factura emitida')
            ->assertSee($admin->name);
    }

    public function test_cashier_sees_operational_dashboard_without_automation(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $cashier = $this->createCashierUser($company, $branch);

        $this->actingAs($cashier)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Productos')
            ->assertDontSee(route('automatizacion.index'));
    }

    public function test_company_dashboard_is_scoped_to_its_company(): void
    {
        $company = $this->createCompany();

        $other = Company::factory()->create();
        $otherBranch = $this->createBranch($other);
        $otherAdmin = $this->createAdminUser($other, $otherBranch);

        $this->actingAs($otherAdmin);
        app(AuditLoggerContract::class)->log(
            'invoice_issued',
            'facturacion',
            'Actividad de otra compañía.',
            userId: $otherAdmin->id,
        );

        $branch = $this->createBranch($company);
        $admin = $this->createAdminUser($company, $branch);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Actividad de otra compañía.');
    }
}
