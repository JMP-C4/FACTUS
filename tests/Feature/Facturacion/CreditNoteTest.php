<?php

declare(strict_types=1);

namespace Tests\Feature\Facturacion;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\Facturacion\Enums\CreditNoteStatus;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\CreditNote;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Facturacion\Models\NumberingRange;
use App\Modules\Productos\Models\Product;
use App\Modules\Reportes\Services\ReportService;
use Tests\Support\HasRoles;
use Tests\TestCase;

class CreditNoteTest extends TestCase
{
    use HasRoles;

    private Company $company;

    private Branch $branch;

    private User $admin;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
        $this->company = $this->createCompany();
        $this->branch = $this->createBranch($this->company);
        $this->admin = $this->createAdminUser($this->company, $this->branch);

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

        $this->product = Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Producto NC',
            'price' => 10000,
            'tax_rate' => 0,
        ]);
    }

    public function test_credit_notes_index_loads(): void
    {
        $this->actingAs($this->admin)
            ->get(route('facturacion.notas.index'))
            ->assertOk()
            ->assertSee('Notas de crédito');
    }

    public function test_issuing_credit_note_creates_applied_record(): void
    {
        $invoice = $this->issueInvoice();

        $this->actingAs($this->admin)
            ->post(route('facturacion.notas.store', $invoice), [
                'correction_concept_code' => '1',
                'reason' => 'Devolución de mercancía',
            ])
            ->assertRedirect(route('facturacion.facturas.show', $invoice));

        $creditNote = CreditNote::firstOrFail();

        $this->assertSame($invoice->id, $creditNote->invoice_id);
        $this->assertSame($this->company->id, $creditNote->company_id);
        $this->assertSame(CreditNoteStatus::Issued, $creditNote->status);
        $this->assertNotNull($creditNote->cufe);
        $this->assertNotNull($creditNote->number);
        $this->assertEquals((float) $invoice->total, (float) $creditNote->total);

        $this->assertDatabaseHas('billing_logs', [
            'loggable_type' => CreditNote::class,
            'loggable_id' => $creditNote->id,
        ]);
    }

    public function test_credit_note_is_rejected_for_draft_invoice(): void
    {
        $this->actingAs($this->admin)->post(route('facturacion.facturas.store'), [
            'items' => [[
                'description' => 'Borrador',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_rate' => 0,
            ]],
            'payments' => [['payment_method_code' => 'cash', 'amount' => 1000, 'cash_received' => 1000, 'cash_change' => 0]],
        ]);

        $invoice = Invoice::firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('facturacion.notas.store', $invoice), [
                'correction_concept_code' => '2',
                'reason' => 'Anulación',
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('credit_notes', 0);
    }

    public function test_credit_note_rejected_when_invoice_already_credited(): void
    {
        $invoice = $this->issueInvoice();

        $this->actingAs($this->admin)->post(route('facturacion.notas.store', $invoice), [
            'correction_concept_code' => '1',
            'reason' => 'Primera nota',
        ]);

        $this->actingAs($this->admin)
            ->post(route('facturacion.notas.store', $invoice), [
                'correction_concept_code' => '1',
                'reason' => 'Segunda nota',
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('credit_notes', 1);
    }

    public function test_credit_note_requires_permission(): void
    {
        $invoice = $this->issueInvoice();
        $cashier = $this->createCashierUser($this->company, $this->branch);

        $this->actingAs($cashier)
            ->post(route('facturacion.notas.store', $invoice), [
                'correction_concept_code' => '1',
                'reason' => 'Sin permiso',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('credit_notes', 0);
    }

    public function test_sales_summary_nets_credit_notes(): void
    {
        $invoice = $this->issueInvoice();

        $this->actingAs($this->admin)->post(route('facturacion.notas.store', $invoice), [
            'correction_concept_code' => '3',
            'reason' => 'Ajuste',
        ]);

        $summary = app(ReportService::class)->salesSummary($this->company->id);

        $this->assertEquals(10000, $summary['total']);
        $this->assertEquals(10000, $summary['credit_notes']);
        $this->assertEquals(0, $summary['net']);
    }

    public function test_invoice_show_displays_credit_note_section(): void
    {
        $invoice = $this->issueInvoice();

        $this->actingAs($this->admin)
            ->post(route('facturacion.notas.store', $invoice), [
                'correction_concept_code' => '1',
                'reason' => 'Motivo visible',
            ]);

        $this->actingAs($this->admin)
            ->get(route('facturacion.facturas.show', $invoice))
            ->assertOk()
            ->assertSee('Notas de crédito')
            ->assertSee('Motivo visible');
    }

    private function issueInvoice(): Invoice
    {
        $this->actingAs($this->admin)->post(route('facturacion.facturas.store'), [
            'items' => [[
                'product_id' => $this->product->id,
                'description' => 'Venta NC',
                'quantity' => 1,
                'unit_price' => 10000,
                'tax_rate' => 0,
            ]],
            'payments' => [['payment_method_code' => 'cash', 'amount' => 10000, 'cash_received' => 10000, 'cash_change' => 0]],
        ]);

        $invoice = Invoice::latest('id')->firstOrFail();

        $this->actingAs($this->admin)->post(route('facturacion.facturas.issue', $invoice));

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Issued, $invoice->status);

        return $invoice;
    }
}
