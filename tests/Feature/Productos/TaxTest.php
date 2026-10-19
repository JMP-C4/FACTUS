<?php

declare(strict_types=1);

namespace Tests\Feature\Productos;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\Productos\Database\Seeders\TaxSeeder;
use App\Modules\Productos\Models\Tax;
use Tests\Support\HasRoles;
use Tests\TestCase;

class TaxTest extends TestCase
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

    public function test_tax_seeder_creates_global_iva_rates(): void
    {
        $this->seed(TaxSeeder::class);

        $this->assertDatabaseHas('taxes', ['company_id' => null, 'code' => 'IVA19', 'rate' => 19]);
        $this->assertDatabaseHas('taxes', ['company_id' => null, 'code' => 'IVA5', 'rate' => 5]);
        $this->assertDatabaseHas('taxes', ['company_id' => null, 'code' => 'IVA0', 'rate' => 0]);
        $this->assertDatabaseHas('taxes', ['company_id' => null, 'code' => 'EXCLUIDO', 'rate' => 0]);
    }

    public function test_super_admin_can_list_taxes(): void
    {
        $superAdmin = $this->createSuperAdmin();

        Tax::factory()->create(['company_id' => null, 'code' => 'IVA19', 'name' => 'IVA 19%']);
        Tax::factory()->create(['company_id' => $this->company->id, 'code' => 'PROPIA', 'name' => 'Propia 10%', 'rate' => 10]);

        $this->actingAs($superAdmin)
            ->get(route('productos.impuestos.index'))
            ->assertOk()
            ->assertSee('IVA 19%')
            ->assertSee('Propia 10%');
    }

    public function test_cashier_cannot_access_taxes(): void
    {
        $cashier = $this->createCashierUser($this->company, $this->branch);

        $this->actingAs($cashier)
            ->get(route('productos.impuestos.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_manage_taxes(): void
    {
        $tax = Tax::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($this->admin)
            ->get(route('productos.impuestos.index'))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->post(route('productos.impuestos.store'), [
                'name' => 'Impuesto 10%',
                'code' => 'IVA10',
                'rate' => 10,
                'type' => 'vat',
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('productos.impuestos.destroy', $tax))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_tax(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)
            ->post(route('productos.impuestos.store'), [
                'name' => 'Impuesto 10%',
                'code' => 'IVA10',
                'rate' => 10,
                'type' => 'vat',
                'is_active' => '1',
            ])
            ->assertRedirect(route('productos.impuestos.index'));

        $this->assertDatabaseHas('taxes', [
            'company_id' => null,
            'code' => 'IVA10',
            'rate' => 10,
        ]);
    }

    public function test_create_tax_validates_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('productos.impuestos.store'), [])
            ->assertSessionHasErrors(['name', 'code', 'rate', 'type']);
    }

    public function test_super_admin_can_update_tax(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $tax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'IVA10',
            'rate' => 10,
        ]);

        $this->actingAs($superAdmin)
            ->put(route('productos.impuestos.update', $tax), [
                'name' => 'Impuesto 12%',
                'code' => 'IVA10',
                'rate' => 12,
                'type' => 'vat',
            ])
            ->assertRedirect(route('productos.impuestos.index'));

        $this->assertDatabaseHas('taxes', ['id' => $tax->id, 'rate' => 12, 'name' => 'Impuesto 12%']);
    }

    public function test_admin_cannot_edit_global_tax(): void
    {
        $tax = Tax::factory()->create(['company_id' => null]);

        $this->actingAs($this->admin)
            ->get(route('productos.impuestos.edit', $tax))
            ->assertForbidden();
    }

    public function test_admin_cannot_update_other_company_tax(): void
    {
        $otherCompany = $this->createCompany();
        $tax = Tax::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($this->admin)
            ->put(route('productos.impuestos.update', $tax), [
                'name' => 'Hack',
                'code' => $tax->code,
                'rate' => 1,
                'type' => 'vat',
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_toggle_and_delete_tax(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $tax = Tax::factory()->create([
            'company_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->patch(route('productos.impuestos.toggleActive', $tax))
            ->assertRedirect(route('productos.impuestos.index'));

        $this->assertDatabaseHas('taxes', ['id' => $tax->id, 'is_active' => false]);

        $this->actingAs($superAdmin)
            ->delete(route('productos.impuestos.destroy', $tax))
            ->assertRedirect(route('productos.impuestos.index'));

        $this->assertDatabaseMissing('taxes', ['id' => $tax->id]);
    }

    public function test_product_creation_syncs_tax_rate_from_selected_tax(): void
    {
        $tax = Tax::factory()->create(['company_id' => null, 'rate' => 5]);

        $this->actingAs($this->admin)
            ->post(route('productos.store'), [
                'name' => 'Producto con IVA',
                'sku' => 'TAXLINK-001',
                'unit_measure_code' => '94',
                'price' => 10000,
                'tax_id' => $tax->id,
                'tax_rate' => 99,
                'minimum_stock' => 0,
            ])
            ->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('products', [
            'company_id' => $this->company->id,
            'sku' => 'TAXLINK-001',
            'tax_id' => $tax->id,
            'tax_rate' => 5,
        ]);
    }
}
