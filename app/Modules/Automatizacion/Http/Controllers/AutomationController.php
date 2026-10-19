<?php

namespace App\Modules\Automatizacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Automatizacion\Services\AutomationRegistry;
use App\Modules\Automatizacion\Services\AutomationRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationController extends Controller
{
    public function __construct(
        private readonly AutomationRegistry $registry,
        private readonly AutomationRunner $runner,
    ) {}

    /**
     * Listado de procesos que el usuario puede ejecutar.
     */
    public function index(Request $request): View
    {
        $processes = $this->registry->runnableFor($request->user());

        return view('automatizacion::index', compact('processes'));
    }

    /**
     * Ejecuta un proceso bajo demanda.
     */
    public function run(Request $request, string $process): RedirectResponse
    {
        $target = $this->registry->find($process);

        abort_if($target === null, 404, 'Proceso no encontrado.');
        abort_unless($request->user()->hasPermission($target->permission()), 403);

        $result = $this->runner->run($target, $request->user());

        return redirect()
            ->route('automatizacion.index')
            ->with($result->ok ? 'success' : 'error', $result->message)
            ->with('automation_result', [
                'Proceso' => $target->label(),
                ...$result->metrics,
            ]);
    }
}
