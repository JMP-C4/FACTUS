<?php

declare(strict_types=1);

namespace Tests\Feature\Productos;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\Productos\Models\Brand;
use Tests\Support\HasRoles;
use Tests\TestCase;

class BrandTest extends TestCase
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

    public function test_admin_can_list_brands(): void
    {
        Brand::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Colgate',
        ]);

        $this->actingAs($this->admin)
            ->get(route('productos.marcas.index'))
            ->assertOk()
            ->assertSee('Colgate');
    }

    public function test_cashier_cannot_access_brands(): void
    {
        $cashier = $this->createCashierUser($this->company, $this->branch);

        $this->actingAs($cashier)
            ->get(route('productos.marcas.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_brand(): void
    {
        $this->actingAs($this->admin)
            ->post(route('productos.marcas.store'), [
                'name' => 'Colombina',
                'is_active' => '1',
            ])
            ->assertRedirect(route('productos.marcas.index'));

        $this->assertDatabaseHas('brands', [
            'company_id' => $this->company->id,
            'name' => 'Colombina',
        ]);
    }

    public function test_create_brand_validates_name_and_uniqueness(): void
    {
        Brand::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Colombina',
        ]);

        $this->actingAs($this->admin)
            ->post(route('productos.marcas.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->actingAs($this->admin)
            ->post(route('productos.marcas.store'), ['name' => 'Colombina'])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_brand(): void
    {
        $brand = Brand::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Colombina',
        ]);

        $this->actingAs($this->admin)
            ->put(route('productos.marcas.update', $brand), ['name' => 'Colombina S.A.'])
            ->assertRedirect(route('productos.marcas.index'));

        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'name' => 'Colombina S.A.']);
    }

    public function test_admin_cannot_manage_other_company_brand(): void
    {
        $otherCompany = $this->createCompany();
        $brand = Brand::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($this->admin)
            ->get(route('productos.marcas.edit', $brand))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('productos.marcas.destroy', $brand))
            ->assertForbidden();
    }

    public function test_admin_can_toggle_and_delete_brand(): void
    {
        $brand = Brand::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($this->admin)
            ->patch(route('productos.marcas.toggleActive', $brand))
            ->assertRedirect(route('productos.marcas.index'));

        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'is_active' => false]);

        $this->actingAs($this->admin)
            ->delete(route('productos.marcas.destroy', $brand))
            ->assertRedirect(route('productos.marcas.index'));

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }
}
