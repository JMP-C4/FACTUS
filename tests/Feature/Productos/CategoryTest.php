<?php

declare(strict_types=1);

namespace Tests\Feature\Productos;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\Productos\Models\Category;
use Tests\Support\HasRoles;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    public function test_admin_can_list_categories(): void
    {
        Category::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Bebidas',
            'slug' => 'bebidas',
        ]);

        $this->actingAs($this->admin)
            ->get(route('productos.categorias.index'))
            ->assertOk()
            ->assertSee('Bebidas')
            ->assertSee('bebidas');
    }

    public function test_cashier_cannot_access_categories(): void
    {
        $cashier = $this->createCashierUser($this->company, $this->branch);

        $this->actingAs($cashier)
            ->get(route('productos.categorias.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_category_with_generated_slug(): void
    {
        $this->actingAs($this->admin)
            ->post(route('productos.categorias.store'), [
                'name' => 'Ropa Deportiva',
                'is_active' => '1',
            ])
            ->assertRedirect(route('productos.categorias.index'));

        $this->assertDatabaseHas('categories', [
            'company_id' => $this->company->id,
            'name' => 'Ropa Deportiva',
            'slug' => 'ropa-deportiva',
        ]);
    }

    public function test_create_category_validates_name_and_uniqueness(): void
    {
        Category::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Bebidas',
        ]);

        $this->actingAs($this->admin)
            ->post(route('productos.categorias.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->actingAs($this->admin)
            ->post(route('productos.categorias.store'), ['name' => 'Bebidas'])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Bebidas',
        ]);

        $this->actingAs($this->admin)
            ->put(route('productos.categorias.update', $category), ['name' => 'Bebidas Alcohólicas'])
            ->assertRedirect(route('productos.categorias.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Bebidas Alcohólicas',
            'slug' => 'bebidas-alcoholicas',
        ]);
    }

    public function test_admin_cannot_manage_other_company_category(): void
    {
        $otherCompany = $this->createCompany();
        $category = Category::factory()->create(['company_id' => $otherCompany->id]);

        $this->actingAs($this->admin)
            ->get(route('productos.categorias.edit', $category))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('productos.categorias.destroy', $category))
            ->assertForbidden();
    }

    public function test_admin_can_toggle_and_delete_category(): void
    {
        $category = Category::factory()->create(['company_id' => $this->company->id]);

        $this->actingAs($this->admin)
            ->patch(route('productos.categorias.toggleActive', $category))
            ->assertRedirect(route('productos.categorias.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'is_active' => false]);

        $this->actingAs($this->admin)
            ->delete(route('productos.categorias.destroy', $category))
            ->assertRedirect(route('productos.categorias.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
