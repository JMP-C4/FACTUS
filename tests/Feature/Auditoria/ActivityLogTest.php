<?php

namespace Tests\Feature\Auditoria;

use App\Models\User;
use App\Modules\Auditoria\Models\ActivityLog;
use Tests\Support\HasRoles;
use Tests\TestCase;

class ActivityLogTest extends TestCase
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
        $this->get(route('auditoria.index'))->assertRedirect(route('login'));
    }

    public function test_super_admin_can_view_audit_log(): void
    {
        ActivityLog::create([
            'user_id' => $this->superAdmin->id,
            'event' => 'created',
            'module' => 'organizacion',
            'description' => 'Compañía creada de prueba',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('auditoria.index'))
            ->assertOk()
            ->assertSee('organizacion');
    }

    public function test_company_creation_is_recorded_in_audit_log(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('empresas.store'), [
                'name' => 'Auditada SA',
                'nit' => '800999888',
                'regime' => 'comun',
                'is_active' => 1,
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'created',
            'module' => 'organizacion',
            'user_id' => $this->superAdmin->id,
        ]);
    }

    public function test_cashier_cannot_view_audit_log(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $cashier = $this->createCashierUser($company, $branch);

        $this->actingAs($cashier)
            ->get(route('auditoria.index'))
            ->assertForbidden();
    }
}
