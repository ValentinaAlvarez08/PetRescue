<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PetReportController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sprint 2 — Comunidad PetRescue
| Sprint 3 — Comunidad por temas, comentarios y filtros (HU-17, HU-18, HU-04)
|--------------------------------------------------------------------------
*/

// HU-15: página principal de la comunidad
Route::get('/', [HomeController::class, 'index'])->name('home');

// Reportes de mascotas (Sprint 1: HU-01, HU-02, HU-03 — mejorados en Sprint 2: HU-13, HU-14, HU-06
// y en Sprint 3: HU-04 búsqueda y filtros en /reportes)
Route::get('/reportes', [PetReportController::class, 'index'])->name('reports.index');
Route::get('/reportes/{report}', [PetReportController::class, 'show'])->whereNumber('report')->name('reports.show');

Route::get('/reportar/{type}', [PetReportController::class, 'create'])
    ->whereIn('type', ['perdida', 'encontrada'])
    ->name('reports.create');
Route::post('/reportar', [PetReportController::class, 'store'])->name('reports.store');

// Gestión privada del reporte a través del enlace con token (sin login)
Route::get('/reporte/{token}', [PetReportController::class, 'manage'])->name('reports.manage');
Route::patch('/reporte/{token}/estado', [PetReportController::class, 'updateStatus'])->name('reports.updateStatus');

// HU-03: API para el radar de mascotas cercanas
Route::get('/api/reportes/cercanos', [PetReportController::class, 'nearby'])->name('reports.nearby');

// HU-16: directorio de servicios con mapa
Route::get('/directorio', [BusinessController::class, 'index'])->name('directory.index');

// Espacios de la comunidad (Sprint 2: HU-15 — Sprint 3: HU-17 publicar, HU-18 comentar)
Route::get('/comunidad/{topic}', [CommunityController::class, 'show'])->name('community.show');
Route::get('/comunidad/{topic}/nueva', [PostController::class, 'create'])->name('posts.create');
Route::post('/comunidad/{topic}', [PostController::class, 'store'])->name('posts.store');
Route::get('/comunidad/{topic}/{post}', [PostController::class, 'show'])->whereNumber('post')->name('posts.show');
Route::post('/comunidad/{topic}/{post}/comentarios', [CommentController::class, 'store'])->whereNumber('post')->name('comments.store');
Route::post('/comunidad/{topic}/{post}/me-sirvio', [PostController::class, 'helpful'])->whereNumber('post')->name('posts.helpful');

// HU-17: gestión privada de la publicación a través del enlace con token (sin login)
Route::get('/publicacion/{token}', [PostController::class, 'manage'])->name('posts.manage');
Route::patch('/publicacion/{token}', [PostController::class, 'update'])->name('posts.update');
Route::delete('/publicacion/{token}', [PostController::class, 'destroy'])->name('posts.destroy');

// Avisos por correo de reportes cercanos
Route::get('/notificaciones', [SubscriberController::class, 'create'])->name('subscribers.create');
Route::post('/notificaciones', [SubscriberController::class, 'store'])->name('subscribers.store');
Route::get('/notificaciones/{token}/baja', [SubscriberController::class, 'unsubscribe'])->name('subscribers.unsubscribe');
