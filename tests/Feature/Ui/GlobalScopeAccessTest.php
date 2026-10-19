<?php

namespace Tests\Feature\Ui;

use App\Models\Company;
use App\Models\User;
use App\Modules\Productos\Models\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\HasRoles;
use Tests\TestCase;

class GlobalScopeAccessTest extends TestCase
{
    use HasRoles;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = $this->createSuperAdmin();
    }

    public static function globalScopedRoutes(): array
    {
        return [
            'productos index' => ['productos.index'],
            'productos create' => ['productos.create'],
            'clientes index' => ['clientes.index'],
            'clientes create' => ['clientes.create'],
            'inventario movimientos' => ['inventario.movimientos.index'],
            'inventario entradas' => ['inventario.entradas.create'],
            'inventario ajustes' => ['inventario.ajustes.create'],
            'reportes ventas' => ['reportes.ventas.index'],
            'reportes inventario' => ['reportes.inventario.index'],
            'facturacion facturas' => ['facturacion.facturas.index'],
            'facturacion create' => ['facturacion.facturas.create'],
            'facturacion pos' => ['facturacion.pos'],
        ];
    }

    #[DataProvider('globalScopedRoutes')]
    public function test_globally_scoped_super_admin_can_access_module_pages(string $route): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route($route))
            ->assertOk();
    }

    public function test_globally_scoped_super_admin_sees_products_from_all_companies(): void
    {
        $firstCompany = Company::factory()->create();
        $secondCompany = Company::factory()->create();

        Product::factory()->create(['company_id' => $firstCompany->id, 'is_active' => true]);
        Product::factory()->create(['company_id' => $secondCompany->id, 'is_active' => true]);

        $this->actingAs($this->superAdmin)
            ->get(route('productos.index'))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 2);
    }

    public function test_globally_scoped_super_admin_cannot_create_product_without_company(): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('productos.create'))
            ->post(route('productos.store'), [
                'name' => 'Producto Global',
                'sku' => 'GLOBAL-001',
                'unit_measure_code' => '94',
                'price' => 1000,
                'tax_rate' => 19,
                'minimum_stock' => 0,
            ])
            ->assertRedirect(route('productos.create'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('products', ['sku' => 'GLOBAL-001']);
    }
}
