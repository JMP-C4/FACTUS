<?php

namespace Tests\Feature\Automatizacion;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\Auditoria\Models\ActivityLog;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\NumberingRange;
use App\Modules\Facturacion\Services\InvoiceService;
use Tests\Support\HasRoles;
use Tests\TestCase;

class AutomationProcessTest extends TestCase
{
    use HasRoles;

    private Company $company;

    private Branch $branch;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->createCompany();
        $this->branch = $this->createBranch($this->company);
        $this->admin = $this->createAdminUser($this->company, $this->branch);
    }

    private function createNumberingRange(): void
    {
        NumberingRange::create([
            'company_id' => $this->company->id,
            'prefix' => 'FAC',
            'resolution_number' => '18760000001',
            'current_number' => 0,
            'from_number' => 1,
            'to_number' => 1000,
            'valid_from' => now()->subDay()->toDateString(),
            'valid_until' => now()->addYear()->toDateString(),
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('automatizacion.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_automation_panel(): void
    {
        $this->actingAs($this->admin)
            ->get(route('automatizacion.index'))
            ->assertOk()
            ->assertSee('Automatización de procesos');
    }

    public function test_cashier_cannot_access_automation_panel(): void
    {
        $cashier = $this->createCashierUser($this->company, $this->branch);

        $this->actingAs($cashier)
            ->get(route('automatizacion.index'))
            ->assertForbidden();
    }

    public function test_unknown_process_returns_not_found(): void
    {
        $this->actingAs($this->admin)
            ->post(route('automatizacion.run', 'no.existe'))
            ->assertNotFound();
    }

    public function test_running_low_stock_process_reports_success(): void
    {
        $this->actingAs($this->admin)
            ->post(route('automatizacion.run', 'inventario.alertas_stock_minimo'))
            ->assertRedirect(route('automatizacion.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'automation.run',
            'module' => 'automatizacion',
        ]);
    }

    public function test_issue_draft_invoices_process_emits_drafts(): void
    {
        $this->createNumberingRange();

        $invoice = app(InvoiceService::class)->create($this->company->id, $this->admin->id, [
            'customer_id' => null,
            'items' => [[
                'description' => 'Servicio de prueba',
                'quantity' => 1,
                'unit_price' => 100000,
                'tax_rate' => 19,
            ]],
            'payments' => [[
                'payment_method_code' => 'cash',
                'amount' => 119000,
            ]],
        ]);

        $this->assertSame(InvoiceStatus::Draft, $invoice->status);

        $this->actingAs($this->admin)
            ->post(route('automatizacion.run', 'facturacion.emitir_borradores'))
            ->assertRedirect(route('automatizacion.index'))
            ->assertSessionHas('success');

        $this->assertSame(InvoiceStatus::Issued, $invoice->fresh()->status);
    }

    public function test_purge_process_removes_only_old_logs(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $old = ActivityLog::create([
            'user_id' => $superAdmin->id,
            'event' => 'created',
            'module' => 'organizacion',
            'description' => 'Registro antiguo',
        ]);
        $old->forceFill(['created_at' => now()->subDays(120)])->save();

        $recent = ActivityLog::create([
            'user_id' => $superAdmin->id,
            'event' => 'created',
            'module' => 'organizacion',
            'description' => 'Registro reciente',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('automatizacion.run', 'plataforma.depurar_bitacora'))
            ->assertRedirect(route('automatizacion.index'));

        $this->assertDatabaseMissing('activity_logs', ['id' => $old->id]);
        $this->assertDatabaseHas('activity_logs', ['id' => $recent->id]);
    }
}
