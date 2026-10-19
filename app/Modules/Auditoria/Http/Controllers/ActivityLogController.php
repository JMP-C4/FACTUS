<?php

namespace App\Modules\Auditoria\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auditoria\Models\ActivityLog;
use App\Modules\Auditoria\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * Listado de la bitácora con filtros.
     */
    public function index(Request $request): View
    {
        $logs = $this->activityLogService->list([
            'search' => $request->input('search'),
            'module' => $request->input('module'),
            'event' => $request->input('event'),
            'user_id' => $request->input('user_id'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ]);

        $modules = $this->activityLogService->modules();
        $events = $this->activityLogService->events();

        return view('auditoria::index', compact('logs', 'modules', 'events'));
    }

    /**
     * Detalle de un evento de auditoría.
     */
    public function show(ActivityLog $log): View
    {
        $log->load(['user', 'subject']);

        return view('auditoria::show', compact('log'));
    }
}
