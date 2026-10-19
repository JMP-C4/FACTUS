<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Ruta raíz: redirige a login
Route::get('/', function () {
    return redirect()->route('login');
});
// Dashboard: solo usuarios autenticados y verificados
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
// Rutas de perfil: solo usuarios autenticados
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// Rutas de autenticación (login, register, reset)
require __DIR__.'/auth.php';
