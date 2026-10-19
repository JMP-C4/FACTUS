<?php

namespace Tests\Feature\Organizacion;

use App\Models\Company;
use App\Models\User;
use Tests\Support\HasRoles;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use HasRoles;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = $this->createSuperAdmin();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('empresas.index'))->assertRedirect(route('login'));
    }

    public function test_super_admin_can_list_companies(): void
    {
        Company::factory()->count(3)->create();

        $this->actingAs($this->superAdmin)
            ->get(route('empresas.index'))
            ->assertOk();
    }

    public function test_super_admin_can_access_create_form(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('empresas.create'))
            ->assertOk();
    }

    public function test_super_admin_can_create_company(): void
    {
        $data = [
            'name' => 'Compañía Nueva',
            'nit' => '900123456',
            'email' => 'contacto@nueva.com',
            'phone' => '3001234567',
            'address' => 'Calle 1 # 2-3',
            'city' => 'Bogotá',
            'regime' => 'comun',
            'is_active' => 1,
            'trial_ends_at' => now()->addDays(10)->format('Y-m-d'),
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('empresas.store'), $data)
            ->assertRedirect(route('empresas.index'));

        $this->assertDatabaseHas('companies', [
            'name' => 'Compañía Nueva',
            'nit' => '900123456',
            'regime' => 'comun',
        ]);
    }

    public function test_super_admin_can_update_company(): void
    {
        $company = Company::factory()->create(['name' => 'Antiguo Nombre']);

        $this->actingAs($this->superAdmin)
            ->put(route('empresas.update', $company), [
                'name' => 'Nombre Nuevo',
                'nit' => $company->nit,
                'regime' => 'comun',
                'is_active' => 1,
            ])
            ->assertRedirect(route('empresas.index'));

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Nombre Nuevo',
        ]);
    }

    public function test_super_admin_can_toggle_company_active(): void
    {
        $company = Company::factory()->create(['is_active' => true]);

        $this->actingAs($this->superAdmin)
            ->patch(route('empresas.toggleActive', $company))
            ->assertRedirect(route('empresas.index'));

        $this->assertFalse($company->fresh()->is_active);
    }

    public function test_super_admin_can_view_company(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->superAdmin)
            ->get(route('empresas.show', $company))
            ->assertOk()
            ->assertSee($company->name);
    }

    public function test_company_admin_cannot_access_companies_module(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $admin = $this->createAdminUser($company, $branch);

        $this->actingAs($admin)
            ->get(route('empresas.index'))
            ->assertForbidden();
    }
}
