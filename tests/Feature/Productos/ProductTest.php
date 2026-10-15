<?php

namespace Tests\Feature\Productos;

use App\Models\Company;
use App\Models\User;
use App\Modules\Productos\Models\Brand;
use App\Modules\Productos\Models\Category;
use App\Modules\Productos\Models\Product;
use Tests\Support\HasRoles;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use HasRoles;

    private Company $company;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
        $this->company = $this->createCompany();
        $branch = $this->createBranch($this->company);
        $this->user = $this->createAdminUser($this->company, $branch);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('productos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_list_products(): void
    {
        Product::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('productos.index'))
            ->assertOk();
    }

    public function test_user_can_access_create_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('productos.create'))
            ->assertOk();
    }

    public function test_super_admin_can_create_product(): void
    {
        $category = Category::factory()->state(['company_id' => $this->company->id])->create();
        $brand = Brand::factory()->state(['company_id' => $this->company->id])->create();

        $data = [
            'name' => 'Producto Test',
            'sku' => 'PROD-001',
            'barcode' => '1234567890123',
            'unit_measure_code' => '94',
            'price' => 50000,
            'tax_rate' => 19,
            'minimum_stock' => 10,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'description' => 'Un producto de prueba',
        ];

        $this->actingAs($this->user)
            ->post(route('productos.store'), $data)
            ->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('products', [
            'company_id' => $this->company->id,
            'name' => 'Producto Test',
            'sku' => 'PROD-001',
            'price' => 50000,
        ]);
    }

    public function test_create_product_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post(route('productos.store'), [])
            ->assertSessionHasErrors(['name', 'sku', 'unit_measure_code', 'price', 'tax_rate', 'minimum_stock']);
    }

    public function test_create_product_validates_sku_uniqueness_per_company(): void
    {
        Product::factory()->create([
            'company_id' => $this->company->id,
            'sku' => 'DUP-001',
        ]);

        $this->actingAs($this->user)
            ->post(route('productos.store'), [
                'name' => 'Otro Producto',
                'sku' => 'DUP-001',
                'unit_measure_code' => '94',
                'price' => 10000,
                'tax_rate' => 19,
                'minimum_stock' => 0,
            ])
            ->assertSessionHasErrors('sku');
    }

    public function test_create_product_allows_same_sku_in_different_company(): void
    {
        $otherCompany = $this->createCompany();

        Product::factory()->create([
            'company_id' => $otherCompany->id,
            'sku' => 'SHARED-SKU',
        ]);

        $this->actingAs($this->user)
            ->post(route('productos.store'), [
                'name' => 'Producto Mismo SKU',
                'sku' => 'SHARED-SKU',
                'unit_measure_code' => '94',
                'price' => 10000,
                'tax_rate' => 19,
                'minimum_stock' => 0,
            ])
            ->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('products', [
            'company_id' => $this->company->id,
            'sku' => 'SHARED-SKU',
        ]);
    }

    public function test_create_product_validates_price_not_negative(): void
    {
        $this->actingAs($this->user)
            ->post(route('productos.store'), [
                'name' => 'Producto Negativo',
                'sku' => 'NEG-001',
                'unit_measure_code' => '94',
                'price' => -100,
                'tax_rate' => 19,
                'minimum_stock' => 0,
            ])
            ->assertSessionHasErrors('price');
    }

    public function test_create_product_validates_tax_rate_max(): void
    {
        $this->actingAs($this->user)
            ->post(route('productos.store'), [
                'name' => 'Producto Impuesto Alto',
                'sku' => 'TAX-001',
                'unit_measure_code' => '94',
                'price' => 10000,
                'tax_rate' => 150,
                'minimum_stock' => 0,
            ])
            ->assertSessionHasErrors('tax_rate');
    }

    public function test_user_can_view_product(): void
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('productos.show', $product))
            ->assertOk();
    }

    public function test_super_admin_can_update_product(): void
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Producto Original',
        ]);

        $this->actingAs($this->user)
            ->put(route('productos.update', $product), [
                'name' => 'Producto Actualizado',
                'sku' => $product->sku,
                'unit_measure_code' => '94',
                'price' => 75000,
                'tax_rate' => 19,
                'minimum_stock' => 5,
            ])
            ->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Producto Actualizado',
        ]);
    }

    public function test_super_admin_can_toggle_product_active_status(): void
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->patch(route('productos.toggleActive', $product))
            ->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }

    public function test_super_admin_can_delete_product(): void
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->actingAs($this->user)
            ->delete(route('productos.destroy', $product))
            ->assertRedirect(route('productos.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_user_cannot_access_other_company_product(): void
    {
        $otherCompany = $this->createCompany();
        $otherProduct = Product::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('productos.show', $otherProduct))
            ->assertForbidden();
    }

    public function test_user_cannot_update_other_company_product(): void
    {
        $otherCompany = $this->createCompany();
        $otherProduct = Product::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        $this->actingAs($this->user)
            ->put(route('productos.update', $otherProduct), [
                'name' => 'Hacked',
                'sku' => $otherProduct->sku,
                'unit_measure_code' => '94',
                'price' => 1,
                'tax_rate' => 0,
                'minimum_stock' => 0,
            ])
            ->assertForbidden();
    }

    public function test_user_can_search_products_by_name(): void
    {
        Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Laptop Gamer',
        ]);

        Product::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Mouse Óptico',
        ]);

        $this->actingAs($this->user)
            ->get(route('productos.index', ['search' => 'Laptop']))
            ->assertOk();
    }
}
