<?php

namespace Tests\Feature\Organizacion;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Tests\Support\HasRoles;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use HasRoles;

    private Company $company;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->createCompany();
        $branch = $this->createBranch($this->company);
        $this->admin = $this->createAdminUser($this->company, $branch);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('sucursales.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_list_own_branches(): void
    {
        Branch::factory()->state(['company_id' => $this->company->id])->count(2)->create();

        $this->actingAs($this->admin)
            ->get(route('sucursales.index'))
            ->assertOk();
    }

    public function test_admin_can_access_create_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('sucursales.create'))
            ->assertOk();
    }

    public function test_admin_can_create_branch(): void
    {
        $data = [
            'name' => 'Sucursal Centro',
            'code' => 'SC-001',
            'city' => 'Medellín',
            'pos_prefix' => '002',
            'resolution_from' => 1,
            'resolution_to' => 100000,
            'is_active' => 1,
        ];

        $this->actingAs($this->admin)
            ->post(route('sucursales.store'), $data)
            ->assertRedirect(route('sucursales.index'));

        $this->assertDatabaseHas('branches', [
            'company_id' => $this->company->id,
            'name' => 'Sucursal Centro',
            'code' => 'SC-001',
        ]);
    }

    public function test_admin_can_update_branch(): void
    {
        $branch = Branch::factory()->state(['company_id' => $this->company->id])->create();

        $this->actingAs($this->admin)
            ->put(route('sucursales.update', $branch), [
                'name' => 'Sucursal Actualizada',
                'code' => $branch->code,
                'pos_prefix' => $branch->pos_prefix,
                'resolution_from' => 1,
                'resolution_to' => 100000,
                'is_active' => 1,
            ])
            ->assertRedirect(route('sucursales.index'));

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Sucursal Actualizada',
        ]);
    }

    public function test_admin_can_toggle_branch_active(): void
    {
        $branch = Branch::factory()->state(['company_id' => $this->company->id, 'is_active' => true])->create();

        $this->actingAs($this->admin)
            ->patch(route('sucursales.toggleActive', $branch))
            ->assertRedirect(route('sucursales.index'));

        $this->assertFalse($branch->fresh()->is_active);
    }

    public function test_admin_cannot_view_other_company_branch(): void
    {
        $otherCompany = $this->createCompany();
        $otherBranch = Branch::factory()->state(['company_id' => $otherCompany->id])->create();

        $this->actingAs($this->admin)
            ->get(route('sucursales.show', $otherBranch))
            ->assertForbidden();
    }
}
