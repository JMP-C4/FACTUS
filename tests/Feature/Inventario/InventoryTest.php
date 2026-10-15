<?php

namespace Tests\Feature\Inventario;

use App\Models\Company;
use App\Models\User;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\StockMovement;
use App\Modules\Inventario\Models\Warehouse;
use App\Modules\Inventario\Services\InventoryMovementGateway;
use App\Modules\Productos\Models\Product;
use Tests\Support\HasRoles;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use HasRoles;

    private Company $company;
    private User $user;
    private Warehouse $warehouse;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
        $this->company = $this->createCompany();
        $branch = $this->createBranch($this->company);
        $this->user = $this->createAdminUser($this->company, $branch);
        $this->warehouse = Warehouse::factory()->state(['company_id' => $this->company->id])->create(['is_default' => true]);
        $this->product = Product::factory()->state(['company_id' => $this->company->id])->create();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('inventario.movimientos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_list_movements(): void
    {
        $this->actingAs($this->user)
            ->get(route('inventario.movimientos.index'))
            ->assertOk();
    }

    public function test_user_can_access_entry_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('inventario.entradas.create'))
            ->assertOk();
    }

    public function test_user_can_create_stock_entry(): void
    {
        $this->actingAs($this->user)
            ->post(route('inventario.entradas.store'), [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'quantity' => 100,
                'reason' => 'Compra inicial',
            ])
            ->assertRedirect(route('inventario.movimientos.index'));

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'movement_type' => 'entry',
            'quantity' => 100,
            'reason' => 'Compra inicial',
        ]);
    }

    public function test_stock_entry_increases_quantity(): void
    {
        Stock::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $this->actingAs($this->user)
            ->post(route('inventario.entradas.store'), [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'quantity' => 30,
                'reason' => 'Reposición',
            ])
            ->assertRedirect(route('inventario.movimientos.index'));

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 80,
        ]);
    }

    public function test_create_entry_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post(route('inventario.entradas.store'), [])
            ->assertSessionHasErrors(['warehouse_id', 'product_id', 'quantity']);
    }

    public function test_create_entry_validates_quantity_minimum(): void
    {
        $this->actingAs($this->user)
            ->post(route('inventario.entradas.store'), [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'quantity' => 0,
            ])
            ->assertSessionHasErrors('quantity');
    }

    public function test_user_can_access_adjustment_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('inventario.ajustes.create'))
            ->assertOk();
    }

    public function test_user_can_create_stock_adjustment(): void
    {
        Stock::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);

        $this->actingAs($this->user)
            ->post(route('inventario.ajustes.store'), [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'new_quantity' => 85,
                'reason' => 'Merma por daño',
            ])
            ->assertRedirect(route('inventario.movimientos.index'));

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 85,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'movement_type' => 'adjustment',
        ]);
    }

    public function test_adjustment_validates_reason_is_required(): void
    {
        $this->actingAs($this->user)
            ->post(route('inventario.ajustes.store'), [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'new_quantity' => 50,
            ])
            ->assertSessionHasErrors('reason');
    }

    public function test_adjustment_validates_new_quantity_not_negative(): void
    {
        $this->actingAs($this->user)
            ->post(route('inventario.ajustes.store'), [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'new_quantity' => -10,
                'reason' => 'Ajuste negativo',
            ])
            ->assertSessionHasErrors('new_quantity');
    }

    public function test_adjustment_can_set_quantity_to_zero(): void
    {
        Stock::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $this->actingAs($this->user)
            ->post(route('inventario.ajustes.store'), [
                'warehouse_id' => $this->warehouse->id,
                'product_id' => $this->product->id,
                'new_quantity' => 0,
                'reason' => 'Producto vencido, todo deteriorado',
            ])
            ->assertRedirect(route('inventario.movimientos.index'));

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);
    }

    public function test_gateway_rejects_exit_with_insufficient_stock(): void
    {
        Stock::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $gateway = app(InventoryMovementGateway::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $gateway->exit(
            $this->warehouse->id,
            $this->product->id,
            50,
            'Salida que excede stock',
            $this->user->id
        );
    }

    public function test_gateway_allows_exit_with_sufficient_stock(): void
    {
        Stock::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);

        $gateway = app(InventoryMovementGateway::class);

        $gateway->exit(
            $this->warehouse->id,
            $this->product->id,
            30,
            'Venta',
            $this->user->id
        );

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 70,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'movement_type' => 'exit',
            'quantity' => -30,
        ]);
    }

    public function test_gateway_reverses_stock_via_adjustment(): void
    {
        Stock::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);

        $gateway = app(InventoryMovementGateway::class);

        $gateway->exit($this->warehouse->id, $this->product->id, 30, 'Venta', $this->user->id);

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 70,
        ]);

        $gateway->adjustment(
            $this->warehouse->id,
            $this->product->id,
            100,
            'Reversión de venta anulada',
            $this->user->id
        );

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'movement_type' => 'adjustment',
            'reason' => 'Reversión de venta anulada',
        ]);
    }

    public function test_gateway_creates_stock_record_if_not_exists(): void
    {
        $gateway = app(InventoryMovementGateway::class);

        $gateway->entry(
            $this->warehouse->id,
            $this->product->id,
            25,
            'Primer ingreso',
            $this->user->id
        );

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 25,
        ]);
    }

    public function test_gateway_has_sufficient_stock_check(): void
    {
        Stock::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $gateway = app(InventoryMovementGateway::class);

        $this->assertTrue(
            $gateway->hasSufficientStock($this->warehouse->id, $this->product->id, 50)
        );

        $this->assertFalse(
            $gateway->hasSufficientStock($this->warehouse->id, $this->product->id, 51)
        );
    }

    public function test_gateway_get_stock_returns_zero_for_unknown_product(): void
    {
        $gateway = app(InventoryMovementGateway::class);

        $result = $gateway->getStock($this->warehouse->id, 99999);

        $this->assertSame(0, $result);
    }

    public function test_user_cannot_create_entry_for_other_company_warehouse(): void
    {
        $otherCompany = $this->createCompany();
        $otherWarehouse = Warehouse::factory()->state(['company_id' => $otherCompany->id])->create();

        $this->actingAs($this->user)
            ->post(route('inventario.entradas.store'), [
                'warehouse_id' => $otherWarehouse->id,
                'product_id' => $this->product->id,
                'quantity' => 10,
                'reason' => 'Intento de acceso no autorizado',
            ])
            ->assertNotFound();
    }
}
