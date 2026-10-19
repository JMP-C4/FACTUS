<?php

namespace App\Modules\Auditoria\Services;

use App\Modules\Auditoria\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActivityLogService
{
    /**
     * Listado paginado de la bitácora con filtros.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = ActivityLog::query()->with('user');

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['module'])) {
            $query->module($filters['module']);
        }

        if (! empty($filters['event'])) {
            $query->event($filters['event']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->latest()->paginate(25);
    }

    /**
     * Módulos registrados en la bitácora (para filtros).
     */
    public function modules(): Collection
    {
        return ActivityLog::query()->distinct()->orderBy('module')->pluck('module');
    }

    /**
     * Eventos registrados en la bitácora (para filtros).
     */
    public function events(): Collection
    {
        return ActivityLog::query()->distinct()->orderBy('event')->pluck('event');
    }
}
