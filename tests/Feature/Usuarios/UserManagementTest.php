<?php

namespace Tests\Feature\Usuarios;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Modules\AccessControl\Models\Permission;
use App\Modules\AccessControl\Models\Role;
use Tests\Support\HasRoles;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use HasRoles;

    private Company $company;

    private Branch $branch;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->createCompany();
        $this->branch = $this->createBranch($this->company);
        $this->admin = $this->createAdminUser($this->company, $this->branch);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('usuarios.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_list_users(): void
    {
        $this->actingAs($this->admin)
            ->get(route('usuarios.index'))
            ->assertOk();
    }

    public function test_admin_can_access_create_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('usuarios.create'))
            ->assertOk();
    }

    public function test_admin_can_create_user_with_role(): void
    {
        $role = Role::where('name', 'cashier')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('usuarios.store'), [
                'name' => 'Nuevo Cajero',
                'email' => 'cajero.nuevo@factus.local',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'role_id' => $role->id,
            ])
            ->assertRedirect();

        $user = User::where('email', 'cajero.nuevo@factus.local')->firstOrFail();

        $this->assertSame($this->company->id, $user->company_id);
        $this->assertTrue($user->hasRole('cashier'));
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->state([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ])->create();

        $this->actingAs($this->admin)
            ->put(route('usuarios.update', $user), [
                'name' => 'Nombre Editado',
                'email' => $user->email,
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
            ])
            ->assertRedirect(route('usuarios.show', $user));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nombre Editado',
        ]);
    }

    public function test_admin_cannot_view_user_of_other_company(): void
    {
        $otherCompany = $this->createCompany();
        $otherUser = User::factory()->state(['company_id' => $otherCompany->id])->create();

        $this->actingAs($this->admin)
            ->get(route('usuarios.show', $otherUser))
            ->assertForbidden();
    }

    public function test_admin_can_grant_extra_permission_only_to_one_user(): void
    {
        $target = $this->createCashierUser($this->company, $this->branch);
        $other = $this->createCashierUser($this->company, $this->branch);
        $permission = Permission::where('name', 'reportes.exportar')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('usuarios.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'permissions' => [$permission->id],
            ])
            ->assertRedirect(route('usuarios.show', $target));

        $this->assertTrue($target->fresh()->hasPermission('reportes.exportar'));
        $this->assertFalse($other->fresh()->hasPermission('reportes.exportar'));
        $this->assertDatabaseHas('permission_user', [
            'user_id' => $target->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_admin_cannot_grant_permission_they_do_not_have(): void
    {
        $target = $this->createCashierUser($this->company, $this->branch);
        $forbidden = Permission::where('name', 'platform.manage')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('usuarios.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'permissions' => [$forbidden->id],
            ])
            ->assertRedirect(route('usuarios.show', $target));

        $this->assertFalse($target->fresh()->hasPermission('platform.manage'));
        $this->assertDatabaseMissing('permission_user', [
            'user_id' => $target->id,
            'permission_id' => $forbidden->id,
        ]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('usuarios.destroy', $this->admin))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
