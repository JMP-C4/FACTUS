<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('access-control')
    ->name('access-control.')
    ->group(function (): void {
        // Las rutas administrativas de access control se agregaran despues.
        // Por ahora el archivo existe para que el modulo quede completo.
    });
