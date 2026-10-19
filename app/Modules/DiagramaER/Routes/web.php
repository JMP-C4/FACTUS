<?php

declare(strict_types=1);

use App\Modules\DiagramaER\Http\Controllers\DiagramaERController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('admin/diagrama-er')
    ->name('diagrama-er.')
    ->group(function () {
        Route::get('/', [DiagramaERController::class, 'index'])->name('index');
        Route::get('/schema', [DiagramaERController::class, 'schema'])->name('schema');
        Route::get('/table/{table}/data', [DiagramaERController::class, 'tableData'])->name('table.data');
        Route::get('/table/{table}/details', [DiagramaERController::class, 'tableDetails'])->name('table.details');
    });
