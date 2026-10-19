<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\AccessControl\Models\Role;
use App\Modules\Clientes\Models\Customer;
use App\Modules\Facturacion\Models\NumberingRange;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\Warehouse;
use App\Modules\Productos\Models\Product;
use App\Modules\Productos\Models\Tax;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Seeder de datos locales de demostración.
 *
 * Crea una compañía lista para operar en local: sucursal, almacén, rango de
 * numeración activo, catálogo de productos, clientes, stock inicial y usuarios
 * con rol (admin y cajero). Idempotente: puede ejecutarse varias veces.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['nit' => '900123456-7'],
            [
                'name' => 'FACTUS Demo S.A.S.',
                'email' => 'contacto@factus.local',
                'phone' => '3000000000',
                'address' => 'Calle 1 # 1-1',
                'city' => 'Bogotá',
                'regime' => 'responsable_iva',
                'is_active' => true,
            ],
        );

        $branch = Branch::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'PRINCIPAL'],
            [
                'name' => 'Sucursal Principal',
                'address' => 'Calle 1 # 1-1',
                'city' => 'Bogotá',
                'phone' => '3000000000',
                'pos_prefix' => 'FAC',
                'allows_negative_stock' => false,
                'is_main' => true,
                'is_warehouse' => false,
                'is_active' => true,
            ],
        );

        $warehouse = Warehouse::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'BODEGA'],
            [
                'name' => 'Bodega Principal',
                'is_default' => true,
            ],
        );

        NumberingRange::firstOrCreate(
            ['company_id' => $company->id, 'prefix' => 'FAC'],
            [
                'resolution_number' => '18760000001',
                'current_number' => 0,
                'from_number' => 1,
                'to_number' => 1000,
                'valid_from' => now()->subDay()->toDateString(),
                'valid_until' => now()->addYear()->toDateString(),
                'is_active' => true,
            ],
        );

        $this->createUsers($company, $branch);
        $products = $this->createProducts($company);
        $this->createCustomers($company);
        $this->createStock($warehouse, $products);
    }

    private function createUsers(Company $company, Branch $branch): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@factus.local'],
            [
                'name' => 'Administrador Demo',
                'password' => 'password',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ],
        );

        $this->markVerified($admin);

        if ($adminRole && ! $admin->hasRole('admin')) {
            $admin->userRoles()->create([
                'role_id' => $adminRole->id,
                'company_id' => $company->id,
                'branch_id' => null,
                'assigned_by' => null,
            ]);
        }

        $cashierRole = Role::where('name', 'cashier')->first();

        $cashier = User::firstOrCreate(
            ['email' => 'cajero@factus.local'],
            [
                'name' => 'Cajero Demo',
                'password' => 'password',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ],
        );

        $this->markVerified($cashier);

        if ($cashierRole && ! $cashier->hasRole('cashier')) {
            $cashier->userRoles()->create([
                'role_id' => $cashierRole->id,
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'assigned_by' => null,
            ]);
        }
    }

    /**
     * Marca al usuario como verificado.
     *
     * El dashboard exige el middleware 'verified'. Los usuarios de demo se
     * crean sin flujo de verificación por email, así que se marcan como
     * verificados para permitir el acceso inmediato. `email_verified_at` no
     * está en los campos asignables masivamente, por eso se asigna directamente.
     */
    private function markVerified(User $user): void
    {
        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
            $user->save();
        }
    }

    /**
     * @return Collection<int, Product>
     */
    private function createProducts(Company $company): Collection
    {
        $products = [
            ['sku' => 'CAF-001', 'barcode' => '7701000000011', 'name' => 'Café molido 500g', 'price' => 18500, 'tax_rate' => 19, 'tax_code' => 'IVA19'],
            ['sku' => 'PAN-001', 'barcode' => '7701000000028', 'name' => 'Pan tajado', 'price' => 6500, 'tax_rate' => 5, 'tax_code' => 'IVA5'],
            ['sku' => 'LEC-001', 'barcode' => '7701000000035', 'name' => 'Leche entera 1L', 'price' => 4200, 'tax_rate' => 0, 'tax_code' => 'IVA0'],
            ['sku' => 'ARR-001', 'barcode' => '7701000000042', 'name' => 'Arroz 500g', 'price' => 3100, 'tax_rate' => 0, 'tax_code' => 'EXCLUIDO'],
            ['sku' => 'GAS-001', 'barcode' => '7701000000059', 'name' => 'Gaseosa 1.5L', 'price' => 5800, 'tax_rate' => 19, 'tax_code' => 'IVA19'],
            ['sku' => 'SNK-001', 'barcode' => '7701000000066', 'name' => 'Papas fritas 105g', 'price' => 4500, 'tax_rate' => 19, 'tax_code' => 'IVA19'],
        ];

        $taxesByCode = Tax::whereNull('company_id')->pluck('id', 'code');

        return collect($products)->map(fn (array $data): Product => Product::updateOrCreate(
            ['company_id' => $company->id, 'sku' => $data['sku']],
            [
                'name' => $data['name'],
                'barcode' => $data['barcode'],
                'price' => $data['price'],
                'tax_id' => $taxesByCode[$data['tax_code']] ?? null,
                'tax_rate' => $data['tax_rate'],
                'unit_measure_code' => '94',
                'minimum_stock' => 5,
                'is_active' => true,
            ],
        ));
    }

    private function createCustomers(Company $company): void
    {
        $customers = [
            [
                'document_number' => '1010101010',
                'first_name' => 'Ana',
                'last_name' => 'Gómez',
                'email' => 'ana.gomez@example.com',
                'phone' => '3101111111',
            ],
            [
                'document_number' => '900555111-2',
                'company_name' => 'Distribuidora El Sol Ltda.',
                'email' => 'compras@elsol.example.com',
                'phone' => '3202222222',
            ],
        ];

        foreach ($customers as $data) {
            Customer::updateOrCreate(
                ['company_id' => $company->id, 'document_number' => $data['document_number']],
                array_merge($data, [
                    'identification_document_code' => '13',
                    'legal_organization_code' => '22',
                    'tribute_code' => 'ZZ',
                    'municipality_code' => '11001',
                    'address' => 'Carrera 2 # 2-2',
                    'is_facturable' => true,
                ]),
            );
        }
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function createStock(Warehouse $warehouse, Collection $products): void
    {
        foreach ($products as $product) {
            Stock::updateOrCreate(
                ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                [
                    'quantity' => 100,
                    'minimum_stock_snapshot' => $product->minimum_stock,
                ],
            );
        }
    }
}
