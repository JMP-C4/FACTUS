<?php

namespace Tests\Feature\Ui;

use App\Models\User;
use Tests\Support\HasRoles;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use HasRoles;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = $this->createSuperAdmin();
    }

    public function test_toast_host_is_rendered_on_pages(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('empresas.index'))
            ->assertOk()
            ->assertSee('$store.toasts.push', false);
    }

    public function test_success_flash_is_shown_as_toast_after_redirect(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('empresas.store'), [
                'name' => 'Toast SA',
                'nit' => '811222333',
                'regime' => 'comun',
                'is_active' => 1,
            ]);

        $response->assertSessionHas('success', 'Compañía creada exitosamente.');

        $this->followingRedirects()
            ->actingAs($this->superAdmin)
            ->get(route('empresas.index'))
            ->assertSee('Compañía creada exitosamente.');
    }

    public function test_error_flash_is_shown_as_banner(): void
    {
        $company = $this->createCompany();
        $branch = $this->createBranch($company);
        $admin = $this->createAdminUser($company, $branch);

        $response = $this->actingAs($admin)
            ->from(route('usuarios.index'))
            ->delete(route('usuarios.destroy', $admin));

        $response->assertSessionHas('error', 'No puedes eliminar tu propio usuario.');

        $this->followingRedirects()
            ->actingAs($admin)
            ->from(route('usuarios.index'))
            ->delete(route('usuarios.destroy', $admin))
            ->assertSee('No puedes eliminar tu propio usuario.');
    }

    public function test_sidebar_includes_collapse_toggle(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('toggleSidebarCollapsed', false)
            ->assertSee('brutal-sidebar-toggle', false);
    }
}
