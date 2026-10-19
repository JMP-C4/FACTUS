<?php

declare(strict_types=1);

namespace Tests\Feature\Inventario;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\Warehouse;
use Tests\Support\HasRoles;
use Tests\TestCase;

class WarehouseTest extends TestCase
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

    public function test_admin_can_list_warehouses(): void
    {
        Warehouse::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Bodega Central',
            'code' => 'BC-01',
        ]);

        $this->actingAs($this->admin)
            ->get(route('inventario.almacenes.index'))
            ->assertOk()
            ->assertSee('Bodega Central')
            ->assertSee('BC-01');
    }

    public function test_cashier_cannot_access_warehouses(): void
    {
        $cashier = $this->createCashierUser($this->company, $this->branch);

        $this->actingAs($cashier)
            ->get(route('inventario.almacenes.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_warehouse(): void
    {
        $this->actingAs($this->admin)
            ->post(route('inventario.almacenes.store'), [
                'name' => 'Bodega Norte',
                'code' => 'BN-01',
                'is_default' => '1',
            ])
            ->assertRedirect(route('inventario.almacenes.index'));

        $this->assertDatabaseHas('warehouses', [
            'company_id' => $this->company->id,
            'code' => 'BN-01',
            'is_default' => true,
        ]);
    }

    public function test_create_warehouse_validates_code_uniqueness(): void
    {
        Warehouse::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'BC-01',
        ]);

        $this->actingAs($this->admin)
            ->post(route('inventario.almacenes.store'), ['name' => 'Otra', 'code' => 'BC-01'])
            ->assertSessionHasErrors('code');
    }

    public function test_setting_default_unsets_previous_default(): void
    {
        $first = Warehouse::factory()->default()->create(['company_id' => $this->company->id]);
        $second = Warehouse::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($this->admin)
            ->patch(route('inventario.almacenes.setDefault', $second))
            ->assertRedirect(route('inventario.almacenes.index'));

        $this->assertDatabaseHas('warehouses', ['id' => $second->id, 'is_default' => true]);
        $this->assertDatabaseHas('warehouses', ['id' => $first->id, 'is_default' => false]);
    }

    public function test_admin_cannot_manage_other_company_warehouse(): void
    {
        $otherCompany = $this->createCompany();
        $warehouse = Warehouse::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($this->admin)
            ->get(route('inventario.almacenes.edit', $warehouse))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('inventario.almacenes.destroy', $warehouse))
            ->assertForbidden();
    }

    public function test_warehouse_with_stock_cannot_be_deleted(): void
    {
        $warehouse = Warehouse::factory()->create(['company_id' => $this->company->id]);
        Stock::factory()->create(['warehouse_id' => $warehouse->id]);

        $this->actingAs($this->admin)
            ->delete(route('inventario.almacenes.destroy', $warehouse))
            ->assertRedirect();

        $this->assertDatabaseHas('warehouses', ['id' => $warehouse->id]);
    }

    public function test_admin_can_delete_empty_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($this->admin)
            ->delete(route('inventario.almacenes.destroy', $warehouse))
            ->assertRedirect(route('inventario.almacenes.index'));

        $this->assertDatabaseMissing('warehouses', ['id' => $warehouse->id]);
    }
}
