<?php

declare(strict_types=1);

namespace Tests\Feature\Reportes;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\StockMovement;
use App\Modules\Inventario\Models\Warehouse;
use App\Modules\Productos\Models\Category;
use App\Modules\Productos\Models\Product;
use App\Modules\Reportes\Services\ReportService;
use Illuminate\Support\Carbon;
use Tests\Support\HasRoles;
use Tests\TestCase;

class ReportTest extends TestCase
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

    public function test_sales_summary_filters_by_cashier(): void
    {
        $cashier = $this->createCashierUser($this->company, $this->branch);
        $this->invoice($this->admin, 'FAC-0001', 'cash');
        $this->invoice($cashier, 'FAC-0002', 'cash');

        $all = app(ReportService::class)->salesSummary($this->company->id);
        $filtered = app(ReportService::class)->salesSummary($this->company->id, null, null, $cashier->id);

        $this->assertSame(2, $all['count']);
        $this->assertSame(1, $filtered['count']);
        $this->assertSame('FAC-0002', $filtered['invoices']->first()->invoice_number);
    }

    public function test_sales_summary_filters_by_payment_method(): void
    {
        $this->invoice($this->admin, 'FAC-0001', 'cash');
        $this->invoice($this->admin, 'FAC-0002', 'transfer');

        $filtered = app(ReportService::class)->salesSummary(
            $this->company->id,
            null,
            null,
            null,
            'transfer',
        );

        $this->assertSame(1, $filtered['count']);
        $this->assertSame('FAC-0002', $filtered['invoices']->first()->invoice_number);
    }

    public function test_sales_report_view_lists_cashiers_and_methods(): void
    {
        $this->invoice($this->admin, 'FAC-0001', 'cash');

        $this->actingAs($this->admin)
            ->get(route('reportes.ventas.index'))
            ->assertOk()
            ->assertSee('FAC-0001')
            ->assertSee('Método de pago')
            ->assertSee('Cajero');
    }

    public function test_sales_summary_includes_previous_period_and_charts_data(): void
    {
        $from = now()->subDays(6)->startOfDay();
        $to = now()->endOfDay();

        $this->invoice($this->admin, 'FAC-0001', 'cash', now());
        $this->invoice($this->admin, 'FAC-0000', 'cash', now()->subDays(10));

        $summary = app(ReportService::class)->salesSummary(
            $this->company->id,
            $from->toDateString(),
            $to->toDateString(),
        );

        $this->assertNotNull($summary['previous']);
        $this->assertSame(1, $summary['previous']['count']);
        $this->assertSame(1, $summary['count']);
        $this->assertCount(1, $summary['daily']);
        $this->assertSame(10000.0, $summary['payment_breakdown']['cash']);
    }

    public function test_sales_report_view_shows_trends_and_charts(): void
    {
        $this->invoice($this->admin, 'FAC-0001', 'cash');

        $this->actingAs($this->admin)
            ->get(route('reportes.ventas.index', [
                'date_from' => now()->startOfMonth()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Ventas por día')
            ->assertSee('Métodos de pago');
    }

    public function test_sales_export_returns_csv(): void
    {
        $this->invoice($this->admin, 'FAC-0001', 'cash');

        $response = $this->actingAs($this->admin)->get(route('reportes.ventas.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('FAC-0001', $response->streamedContent());
    }

    public function test_inventory_export_returns_csv(): void
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Café',
            'sku' => 'CAF-001',
        ]);
        $warehouse = Warehouse::factory()->create(['company_id' => $this->company->id]);
        Stock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reportes.inventario.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('CAF-001', $response->streamedContent());
        $this->assertStringContainsString('Último movimiento', $response->streamedContent());
    }

    public function test_inventory_report_flags_low_rotation(): void
    {
        [$warehouse, $product] = $this->stockedProduct();
        $this->movement($warehouse->id, $product->id, 45);

        $report = app(ReportService::class)->inventoryReport(
            $this->company->id,
            null,
            'low_rotation',
        );

        $this->assertSame(1, $report['stocks']->count());
        $this->assertSame(1, $report['low_rotation']);
        $this->assertTrue($report['stocks']->first()->is_low_rotation);
        $this->assertNotNull($report['stocks']->first()->last_movement_at);
    }

    public function test_inventory_report_excludes_recent_movements_from_low_rotation(): void
    {
        [$warehouse, $product] = $this->stockedProduct();
        $this->movement($warehouse->id, $product->id, 3);

        $report = app(ReportService::class)->inventoryReport(
            $this->company->id,
            null,
            'low_rotation',
        );

        $this->assertSame(0, $report['stocks']->count());
        $this->assertSame(0, $report['low_rotation']);
    }

    public function test_inventory_view_shows_low_rotation_filter(): void
    {
        [$warehouse, $product] = $this->stockedProduct();
        $this->movement($warehouse->id, $product->id, 45);

        $this->actingAs($this->admin)
            ->get(route('reportes.inventario.index', ['status' => 'low_rotation']))
            ->assertOk()
            ->assertSee('Baja Rotación')
            ->assertSee($product->name);
    }

    public function test_inventory_report_filters_by_category(): void
    {
        $warehouse = Warehouse::factory()->create(['company_id' => $this->company->id]);

        $categoryA = Category::factory()->create(['company_id' => $this->company->id, 'name' => 'Bebidas']);
        $categoryB = Category::factory()->create(['company_id' => $this->company->id, 'name' => 'Snacks']);

        $productA = Product::factory()->create([
            'company_id' => $this->company->id,
            'category_id' => $categoryA->id,
            'name' => 'Café molido',
        ]);
        $productB = Product::factory()->create([
            'company_id' => $this->company->id,
            'category_id' => $categoryB->id,
            'name' => 'Papas',
        ]);

        Stock::factory()->create(['warehouse_id' => $warehouse->id, 'product_id' => $productA->id, 'quantity' => 10]);
        Stock::factory()->create(['warehouse_id' => $warehouse->id, 'product_id' => $productB->id, 'quantity' => 10]);

        $report = app(ReportService::class)->inventoryReport(
            $this->company->id,
            null,
            null,
            $categoryA->id,
        );

        $this->assertSame(1, $report['stocks']->count());
        $this->assertSame('Café molido', $report['stocks']->first()->product->name);
        $this->assertSame(1, $report['with_stock']);
    }

    public function test_inventory_view_shows_category_filter(): void
    {
        [, $product] = $this->stockedProduct();

        $this->actingAs($this->admin)
            ->get(route('reportes.inventario.index'))
            ->assertOk()
            ->assertSee('name="category_id"', false)
            ->assertSee('Categoría')
            ->assertSee($product->name);
    }

    public function test_cashier_cannot_export_reports(): void
    {
        $cashier = $this->createCashierUser($this->company, $this->branch);

        $this->actingAs($cashier)
            ->get(route('reportes.ventas.export'))
            ->assertForbidden();

        $this->actingAs($cashier)
            ->get(route('reportes.inventario.export'))
            ->assertForbidden();
    }

    /**
     * @return array{0: Warehouse, 1: Product}
     */
    private function stockedProduct(): array
    {
        $warehouse = Warehouse::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Producto baja rotación',
        ]);

        Stock::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'minimum_stock_snapshot' => 2,
        ]);

        return [$warehouse, $product];
    }

    private function movement(int $warehouseId, int $productId, int $daysAgo): StockMovement
    {
        $movement = StockMovement::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'movement_type' => 'entry',
            'quantity' => 5,
        ]);

        $movement->created_at = now()->subDays($daysAgo);
        $movement->save();

        return $movement;
    }

    private function invoice(User $user, string $number, string $method, ?Carbon $date = null): Invoice
    {
        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'user_id' => $user->id,
            'status' => InvoiceStatus::Issued->value,
            'invoice_number' => $number,
            'issued_at' => now(),
            'subtotal' => 10000,
            'tax_total' => 0,
            'total' => 10000,
            'payment_method_code' => $method,
        ]);

        if ($date !== null) {
            $invoice->created_at = $date;
            $invoice->save();
        }

        $invoice->payments()->create([
            'payment_method_code' => $method,
            'amount' => 10000,
        ]);

        return $invoice;
    }
}
