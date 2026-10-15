<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reportes\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ReportesController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
    ) {}

    public function ventas(Request $request): View
    {
        $summary = $this->reports->salesSummary(
            $request->user()->company_id,
            $request->input('date_from'),
            $request->input('date_to'),
        );

        return view('reportes::ventas.index', $summary);
    }

    public function inventario(Request $request): View
    {
        $report = $this->reports->inventoryReport(
            $request->user()->company_id,
            $request->integer('warehouse_id') ?: null,
            $request->input('status'),
        );

        return view('reportes::inventario.index', $report);
    }
}
