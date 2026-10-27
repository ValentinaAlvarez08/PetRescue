<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\FlagController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\PetProfileController;
use App\Http\Controllers\PetReportController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sprint 2 — Comunidad PetRescue
| Sprint 3 — Comunidad por temas, comentarios y filtros (HU-17, HU-18, HU-04)
| Sprint 4 — Alertas de varias zonas, compartir y estadísticas (HU-11, HU-09, HU-12)
|            Coincidencias, edición, denuncias, perfil, negocios y guías
|            (HU-05, HU-08, HU-07, HU-20, HU-10, HU-19, HU-21)
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
// HU-07: editar los datos, marcar reunido, cerrar o reabrir
Route::get('/reporte/{token}', [PetReportController::class, 'manage'])->name('reports.manage');
Route::get('/reporte/{token}/editar', [PetReportController::class, 'edit'])->name('reports.edit');
Route::patch('/reporte/{token}', [PetReportController::class, 'update'])->name('reports.update');
Route::patch('/reporte/{token}/estado', [PetReportController::class, 'updateStatus'])->name('reports.updateStatus');

// HU-03: API para el radar de mascotas cercanas
Route::get('/api/reportes/cercanos', [PetReportController::class, 'nearby'])->name('reports.nearby');
// HU-05: reportes similares mientras se llena el formulario
Route::get('/api/reportes/similares', [PetReportController::class, 'similar'])->name('reports.similar');

// HU-10: perfil digital de la mascota (enlace público con código corto, gestión con token)
Route::get('/mi-mascota/nueva', [PetProfileController::class, 'create'])->name('profiles.create');
Route::post('/mi-mascota', [PetProfileController::class, 'store'])->name('profiles.store');
Route::get('/mi-mascota/{token}', [PetProfileController::class, 'manage'])->whereUuid('token')->name('profiles.manage');
Route::patch('/mi-mascota/{token}', [PetProfileController::class, 'update'])->whereUuid('token')->name('profiles.update');
Route::delete('/mi-mascota/{token}', [PetProfileController::class, 'destroy'])->whereUuid('token')->name('profiles.destroy');
Route::get('/mascota/{profile}', [PetProfileController::class, 'show'])->name('profiles.show');

// HU-16: directorio de servicios con mapa
Route::get('/directorio', [BusinessController::class, 'index'])->name('directory.index');
// HU-19: solicitar el registro de un negocio
Route::get('/directorio/registrar', [BusinessController::class, 'create'])->name('directory.create');
Route::post('/directorio/registrar', [BusinessController::class, 'store'])->name('directory.store');

// HU-21: guías rápidas por tema
Route::get('/guias', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guias/{slug}', [GuideController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('guides.show');

// HU-20: denunciar contenido inapropiado (máximo 10 por minuto por persona)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/reportes/{report}/denunciar', [FlagController::class, 'report'])->whereNumber('report')->name('flags.report');
    Route::post('/comunidad/{topic}/{post}/denunciar', [FlagController::class, 'post'])->whereNumber('post')->name('flags.post');
    Route::post('/comentarios/{comment}/denunciar', [FlagController::class, 'comment'])->whereNumber('comment')->name('flags.comment');
});

// HU-19 / HU-20: moderación con los enlaces firmados que llegan al correo de moderación
Route::middleware('signed')->prefix('moderacion')->group(function () {
    Route::get('/negocios/{business}', [ModerationController::class, 'showBusiness'])->name('moderation.businesses.show');
    Route::post('/negocios/{business}', [ModerationController::class, 'reviewBusiness']);
    Route::get('/contenido/{type}/{id}', [ModerationController::class, 'showFlagged'])->whereNumber('id')->name('moderation.flagged.show');
    Route::post('/contenido/{type}/{id}', [ModerationController::class, 'restoreFlagged'])->whereNumber('id');
});

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

// Avisos por correo de reportes cercanos (Sprint 4 — HU-11: varias zonas por correo)
Route::get('/notificaciones', [SubscriberController::class, 'create'])->name('subscribers.create');
Route::post('/notificaciones', [SubscriberController::class, 'store'])->name('subscribers.store');
Route::get('/notificaciones/{token}/baja', [SubscriberController::class, 'unsubscribe'])->name('subscribers.unsubscribe');

// HU-11: administración de las zonas a través del enlace con token (sin login)
Route::get('/alertas/{token}', [SubscriberController::class, 'manage'])->name('subscribers.manage');
Route::post('/alertas/{token}/zonas', [SubscriberController::class, 'storeZone'])->name('subscribers.zones.store');
Route::patch('/alertas/{token}/zonas/{zone}', [SubscriberController::class, 'updateZone'])->whereNumber('zone')->name('subscribers.zones.update');
Route::delete('/alertas/{token}/zonas/{zone}', [SubscriberController::class, 'destroyZone'])->whereNumber('zone')->name('subscribers.zones.destroy');

// HU-12: estadísticas de reencuentros
Route::get('/estadisticas', [StatsController::class, 'index'])->name('stats.index');
