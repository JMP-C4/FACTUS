<?php

namespace App\Modules\Usuarios\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use App\Modules\Usuarios\Http\Requests\StoreUserRequest;
use App\Modules\Usuarios\Http\Requests\UpdateUserRequest;
use App\Modules\Usuarios\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AuditLoggerContract $auditLogger,
    ) {}

    public function index(Request $request): View
    {
        $users = $this->userService->list($this->scopedCompanyId($request), [
            'search' => $request->input('search'),
            'role' => $request->input('role'),
        ]);

        return view('usuarios::index', [
            'users' => $users,
            'roles' => $this->userService->roles(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('usuarios::create', [
            'roles' => $this->userService->roles(),
            'companies' => $this->userService->companies(),
            'branches' => $this->userService->branches(),
            'permissionsGroupedByModule' => $this->userService->permissionsGroupedByModule(),
            'rolePermissionIds' => [],
            'directPermissionIds' => [],
            'grantablePermissionIds' => $this->userService->grantablePermissionIds($request->user()),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->userService->create($request->validated());

        $this->userService->syncDirectPermissions(
            $user,
            $this->permissionIdsToSync($request, $this->userService->grantablePermissionIds($request->user())),
        );

        $this->auditLogger->log('created', 'usuarios', "Usuario creado: {$user->email}", $user);

        return redirect()
            ->route('usuarios.show', $user)
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(Request $request, User $user): View
    {
        $this->authorizeUser($request, $user);

        $user->load(['company', 'branch', 'roles.permissions', 'directPermissions']);

        return view('usuarios::show', compact('user'));
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeUser($request, $user);

        return view('usuarios::edit', [
            'user' => $user->load('roles'),
            'roles' => $this->userService->roles(),
            'companies' => $this->userService->companies(),
            'branches' => $this->userService->branches(),
            'permissionsGroupedByModule' => $this->userService->permissionsGroupedByModule(),
            'rolePermissionIds' => $this->userService->rolePermissionIds($user),
            'directPermissionIds' => $this->userService->directPermissionIds($user),
            'grantablePermissionIds' => $this->userService->grantablePermissionIds($request->user(), $user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorizeUser($request, $user);

        $this->userService->update($user, $request->validated());

        $this->userService->syncDirectPermissions(
            $user,
            $this->permissionIdsToSync($request, $this->userService->grantablePermissionIds($request->user(), $user)),
        );

        $this->auditLogger->log('updated', 'usuarios', "Usuario actualizado: {$user->email}", $user);

        return redirect()
            ->route('usuarios.show', $user)
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUser($request, $user);

        if ($request->user()->is($user)) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $email = $user->email;
        $this->userService->delete($user);

        $this->auditLogger->log('deleted', 'usuarios', "Usuario eliminado: {$email}", $user);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Compañía por la que se restringe el listado (null = global).
     */
    private function scopedCompanyId(Request $request): ?int
    {
        return $request->user()->isSuperAdmin() ? null : $request->user()->company_id;
    }

    /**
     * Impide que un usuario administre usuarios de otra compañía.
     */
    private function authorizeUser(Request $request, User $user): void
    {
        if ($request->user()->isSuperAdmin()) {
            return;
        }

        if ($user->company_id !== $request->user()->company_id) {
            throw new AccessDeniedHttpException('No tienes acceso a este usuario.');
        }
    }

    /**
     * IDs de permisos extra que se pueden asignar, limitados a los permitidos
     * para el actor. Un actor no puede otorgar permisos que él mismo no posee.
     *
     * @param  array<int, int>|null  $grantable
     * @return array<int, int>
     */
    private function permissionIdsToSync(Request $request, ?array $grantable): array
    {
        $ids = collect($request->input('permissions', []))
            ->map(fn ($id): int => (int) $id);

        if ($grantable !== null) {
            $ids = $ids->intersect($grantable);
        }

        return $ids->unique()->values()->all();
    }
}
