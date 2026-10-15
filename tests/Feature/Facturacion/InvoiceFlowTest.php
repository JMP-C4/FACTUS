<?php

namespace Tests\Feature\Facturacion;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Facturacion\Models\NumberingRange;
use App\Modules\Inventario\Models\Warehouse;
use App\Modules\Productos\Models\Product;
use Tests\Support\HasRoles;
use Tests\TestCase;

class InvoiceFlowTest extends TestCase
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
            'name' => 'Producto POS',
            'price' => 10000,
            'tax_rate' => 19,
        ]);
    }

    public function test_pos_page_loads_for_authorized_user(): void
    {
        $this->actingAs($this->admin)
            ->get(route('facturacion.pos'))
            ->assertOk()
            ->assertSee('Carrito');
    }

    public function test_product_search_returns_json(): void
    {
        $this->actingAs($this->admin)
            ->getJson(route('facturacion.pos.productos', ['term' => 'Producto']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Producto POS']);
    }

    public function test_checkout_creates_and_issues_invoice(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('facturacion.pos.checkout'), [
                'payment_form_code' => '1',
                'payment_method_code' => 'cash',
                'items' => [[
                    'product_id' => $this->product->id,
                    'description' => $this->product->name,
                    'quantity' => 2,
                    'unit_price' => 10000,
                    'tax_rate' => 19,
                ]],
                'payments' => [[
                    'payment_method_code' => 'cash',
                    'amount' => 23800,
                ]],
            ])
            ->assertCreated()
            ->assertJsonPath('invoice.status', InvoiceStatus::Issued->value);

        $invoice = Invoice::firstOrFail();

        $this->assertSame(InvoiceStatus::Issued, $invoice->status);
        $this->assertNotNull($invoice->cufe);
        $this->assertNotNull($invoice->qr_url);
        $this->assertEquals(23800, (float) $invoice->total);
        $this->assertCount(1, $invoice->items);
        $this->assertCount(1, $invoice->payments);
    }

    public function test_checkout_requires_items(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('facturacion.pos.checkout'), [
                'items' => [],
                'payments' => [['payment_method_code' => 'cash', 'amount' => 0]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');
    }

    public function test_cashier_checkout_omits_invoice_url(): void
    {
        $cashier = $this->createCashierUser($this->company, $this->branch);

        $this->actingAs($cashier)
            ->postJson(route('facturacion.pos.checkout'), [
                'items' => [[
                    'product_id' => $this->product->id,
                    'description' => $this->product->name,
                    'quantity' => 1,
                    'unit_price' => 10000,
                    'tax_rate' => 0,
                ]],
                'payments' => [['payment_method_code' => 'cash', 'amount' => 10000]],
            ])
            ->assertCreated()
            ->assertJsonPath('invoice.url', null);
    }

    public function test_manual_invoice_can_be_created_and_issued(): void
    {
        $this->actingAs($this->admin)
            ->post(route('facturacion.facturas.store'), [
                'payment_form_code' => '1',
                'payment_method_code' => 'cash',
                'items' => [[
                    'product_id' => $this->product->id,
                    'description' => 'Venta manual',
                    'quantity' => 1,
                    'unit_price' => 5000,
                    'tax_rate' => 0,
                ]],
                'payments' => [['payment_method_code' => 'cash', 'amount' => 5000]],
            ])
            ->assertRedirect();

        $invoice = Invoice::firstOrFail();
        $this->assertTrue($invoice->isDraft());

        $this->actingAs($this->admin)
            ->post(route('facturacion.facturas.issue', $invoice))
            ->assertRedirect(route('facturacion.facturas.show', $invoice));

        $this->assertSame(InvoiceStatus::Issued, $invoice->fresh()->status);
    }

    public function test_draft_invoice_can_be_cancelled(): void
    {
        $this->actingAs($this->admin)->post(route('facturacion.facturas.store'), [
            'items' => [[
                'description' => 'Borrador',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_rate' => 0,
            ]],
            'payments' => [['payment_method_code' => 'cash', 'amount' => 1000]],
        ]);

        $invoice = Invoice::firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('facturacion.facturas.cancel', $invoice))
            ->assertRedirect(route('facturacion.facturas.show', $invoice));

        $this->assertSame(InvoiceStatus::Cancelled, $invoice->fresh()->status);
    }

    public function test_verification_page_loads_for_issued_invoice(): void
    {
        $this->actingAs($this->admin)->post(route('facturacion.facturas.store'), [
            'items' => [[
                'description' => 'Verificable',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_rate' => 0,
            ]],
            'payments' => [['payment_method_code' => 'cash', 'amount' => 1000]],
        ]);

        $invoice = Invoice::firstOrFail();
        $this->actingAs($this->admin)->post(route('facturacion.facturas.issue', $invoice));

        $this->get(route('facturacion.verificar', $invoice->fresh()->cufe))
            ->assertOk()
            ->assertSee($invoice->invoice_number);
    }

    public function test_invoice_list_and_show_pages_load(): void
    {
        $this->actingAs($this->admin)->post(route('facturacion.facturas.store'), [
            'items' => [[
                'description' => 'Listable',
                'quantity' => 1,
                'unit_price' => 1000,
                'tax_rate' => 0,
            ]],
            'payments' => [['payment_method_code' => 'cash', 'amount' => 1000]],
        ]);

        $invoice = Invoice::firstOrFail();

        $this->actingAs($this->admin)->get(route('facturacion.facturas.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('facturacion.facturas.show', $invoice))->assertOk();
        $this->actingAs($this->admin)->get(route('facturacion.facturas.create'))->assertOk();
    }

    public function test_sales_and_inventory_reports_load_with_data(): void
    {
        $this->actingAs($this->admin)->post(route('facturacion.facturas.store'), [
            'items' => [[
                'description' => 'Reporte',
                'quantity' => 3,
                'unit_price' => 10000,
                'tax_rate' => 19,
            ]],
            'payments' => [['payment_method_code' => 'cash', 'amount' => 35700]],
        ]);
        $invoice = Invoice::firstOrFail();
        $this->actingAs($this->admin)->post(route('facturacion.facturas.issue', $invoice));

        Warehouse::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($this->admin)
            ->get(route('reportes.ventas.index'))
            ->assertOk()
            ->assertSee($invoice->invoice_number);

        $this->actingAs($this->admin)
            ->get(route('reportes.inventario.index'))
            ->assertOk();
    }

    public function test_invoice_routes_require_authentication(): void
    {
        $this->get(route('facturacion.facturas.index'))->assertRedirect(route('login'));
        $this->get(route('facturacion.pos'))->assertRedirect(route('login'));
        $this->get(route('reportes.ventas.index'))->assertRedirect(route('login'));
    }

    public function test_dashboard_renders_module_navigation(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('POS')
            ->assertSee('Facturas')
            ->assertSee('Inventario')
            ->assertSee('Reportes');
    }
}
