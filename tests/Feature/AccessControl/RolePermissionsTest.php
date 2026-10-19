<?php

namespace Tests\Feature\AccessControl;

use App\Models\Company;
use App\Models\User;
use App\Modules\AccessControl\Models\Permission;
use App\Modules\AccessControl\Models\Role;
use Tests\Support\HasRoles;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use HasRoles;

    private User $superAdmin;

    private User $admin;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = $this->createSuperAdmin();
        $this->company = $this->createCompany();
        $branch = $this->createBranch($this->company);
        $this->admin = $this->createAdminUser($this->company, $branch);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('roles.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_list_roles(): void
    {
        $this->actingAs($this->admin)
            ->get(route('roles.index'))
            ->assertOk();
    }

    public function test_super_admin_can_edit_role_permissions(): void
    {
        $role = Role::where('name', 'cashier')->firstOrFail();
        $permissionIds = Permission::whereIn('name', ['productos.view', 'pos.vender'])->pluck('id')->all();

        $this->actingAs($this->superAdmin)
            ->put(route('roles.update', $role), ['permissions' => $permissionIds])
            ->assertRedirect(route('roles.index'));

        $this->assertEqualsCanonicalizing(
            $permissionIds,
            $role->fresh()->permissions->pluck('id')->all()
        );
    }

    public function test_admin_cannot_edit_role_permissions(): void
    {
        $role = Role::where('name', 'cashier')->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('roles.edit', $role))
            ->assertForbidden();
    }
}
