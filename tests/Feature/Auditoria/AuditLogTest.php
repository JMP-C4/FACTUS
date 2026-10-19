<?php

declare(strict_types=1);

namespace Tests\Feature\Auditoria;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\Auditoria\Models\ActivityLog;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Facturacion\Models\NumberingRange;
use App\Modules\Productos\Models\Product;
use Tests\Support\HasRoles;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use HasRoles;

    private Company $company;

    private Branch $branch;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
        $this->company = $this->createCompany();
        $this->branch = $this->createBranch($this->company);
        $this->admin = $this->createAdminUser($this->company, $this->branch);
    }

    public function test_successful_login_is_recorded(): void
    {
        $user = User::factory()->create([
            'email' => 'cajero@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'event' => 'login',
            'module' => 'auth',
            'subject_type' => $user->getMorphClass(),
            'subject_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('activity_logs', [
            'description' => 'secret123',
        ]);
    }

    public function test_failed_login_is_recorded(): void
    {
        $this->post(route('login'), [
            'email' => 'intruso@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'login_failed',
            'module' => 'auth',
        ]);

        $this->assertDatabaseMissing('activity_logs', [
            'description' => 'wrong-password',
        ]);
    }

    public function test_issuing_invoice_is_recorded(): void
    {
        $this->numberingRange();
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Producto auditoría',
            'price' => 10000,
            'tax_rate' => 0,
        ]);

        $this->actingAs($this->admin)->post(route('facturacion.facturas.store'), [
            'items' => [[
                'product_id' => $product->id,
                'description' => 'Venta auditada',
                'quantity' => 1,
                'unit_price' => 10000,
                'tax_rate' => 0,
            ]],
            'payments' => [['payment_method_code' => 'cash', 'amount' => 10000, 'cash_received' => 10000, 'cash_change' => 0]],
        ]);

        $invoice = Invoice::firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('facturacion.facturas.issue', $invoice));

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'invoice_issued',
            'module' => 'facturacion',
            'subject_type' => $invoice->getMorphClass(),
            'subject_id' => $invoice->id,
        ]);

        $log = ActivityLog::where('event', 'invoice_issued')->firstOrFail();
        $this->assertSame('issued', $log->properties['status']);
    }

    public function test_issuing_credit_note_is_recorded(): void
    {
        $this->numberingRange();
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Producto auditoría NC',
            'price' => 10000,
            'tax_rate' => 0,
        ]);

        $this->actingAs($this->admin)->post(route('facturacion.facturas.store'), [
            'items' => [[
                'product_id' => $product->id,
                'description' => 'Venta auditada',
                'quantity' => 1,
                'unit_price' => 10000,
                'tax_rate' => 0,
            ]],
            'payments' => [['payment_method_code' => 'cash', 'amount' => 10000, 'cash_received' => 10000, 'cash_change' => 0]],
        ]);

        $invoice = Invoice::firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('facturacion.facturas.issue', $invoice));

        $this->actingAs($this->admin)
            ->post(route('facturacion.notas.store', $invoice), [
                'correction_concept_code' => '1',
                'reason' => 'Devolución auditada',
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'credit_note_issued',
            'module' => 'facturacion',
        ]);
    }

    private function numberingRange(): void
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
}
