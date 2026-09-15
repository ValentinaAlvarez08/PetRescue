<?php

use App\Http\Controllers\PetReportController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Sprint 1 — HU1, HU2, HU3
|--------------------------------------------------------------------------
| Copia estas rutas dentro de tu routes/web.php (reemplaza la ruta de
| bienvenida por defecto que trae Laravel, o agrega estas debajo).
*/

Route::get('/', [PetReportController::class, 'index'])->name('reports.index');

// HU1 y HU2: formulario según el tipo (perdida | encontrada)
Route::get('/reportar/{type}', [PetReportController::class, 'create'])
    ->whereIn('type', ['perdida', 'encontrada'])
    ->name('reports.create');

Route::post('/reportar', [PetReportController::class, 'store'])->name('reports.store');

// Vista y gestión de un reporte a través del enlace privado (sin login)
Route::get('/reporte/{token}', [PetReportController::class, 'show'])->name('reports.show');
Route::patch('/reporte/{token}/estado', [PetReportController::class, 'updateStatus'])->name('reports.updateStatus');

// HU3: API para el radar de mascotas cercanas
Route::get('/api/reportes/cercanos', [PetReportController::class, 'nearby'])->name('reports.nearby');

// HU3: notificación automática por correo cuando se publica un reporte cercano
Route::get('/notificaciones', [SubscriberController::class, 'create'])->name('subscribers.create');
Route::post('/notificaciones', [SubscriberController::class, 'store'])->name('subscribers.store');
Route::get('/notificaciones/{token}/baja', [SubscriberController::class, 'unsubscribe'])->name('subscribers.unsubscribe');
