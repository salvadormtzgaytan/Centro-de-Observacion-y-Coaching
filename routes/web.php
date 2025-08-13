<?php

use App\Http\Controllers\Coach\EvaluationSessionController;

use App\Http\Controllers\Coachee\CoacheeSessionController;
use App\Http\Controllers\EvaluationPdfController;
use App\Livewire\Coach\EvaluationHistory;
use App\Livewire\Coach\EvaluationSessionFilling;
use App\Livewire\Coach\EvaluationWizard;
use FontLib\Table\Type\post;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});
// 🧭 Rutas del flujo para Coach
Route::middleware(['auth', 'role:coach'])->group(function () {
    // Listado de sesiones en curso
    Route::get('evaluations', [EvaluationSessionController::class, 'index'])
        ->name('evaluation.index');

    // Creación de sesión vía Livewire wizard
    Route::match(['get', 'post'], 'evaluations/create', EvaluationWizard::class)
        ->name('evaluation.create');

    // Historial de sesiones completadas/firmadas
    Route::get('evaluations/history', EvaluationHistory::class)
        ->name('evaluation.history');

    // Solo elimina (destroy)
    Route::delete('evaluations/{evaluation}', [EvaluationSessionController::class, 'destroy'])
        ->name('evaluation.destroy');

    Route::get('evaluations/{session}/summary', [EvaluationSessionController::class, 'summary'])
        ->name('evaluation.summary');

    Route::get('evaluations/{session}/fill', EvaluationSessionFilling::class)
        ->name('evaluation.fill');
});

// 🧭 Rutas del flujo para Coachee
Route::prefix('coachee')
    ->name('coachee.')
    ->middleware(['auth', 'role:coachee'])
    ->group(function () {
        // Index: vista que monta el componente Livewire <livewire:coachee.sessions-index />
        Route::view('sessions', 'coachee.sessions.index')->name('sessions.index'); // soporta ?filter=pending
        Route::get('sessions/{session}', [CoacheeSessionController::class, 'show'])->name('sessions.show');
        Route::get('sessions/{session}/sign', [CoacheeSessionController::class, 'signForm'])->name('sessions.sign.form');
        Route::post('sessions/{session}/sign', [CoacheeSessionController::class, 'sign'])->name('sessions.sign');
        Route::post('sessions/{session}/reject', [CoacheeSessionController::class, 'reject'])->name('sessions.reject');
        Route::get('sessions/{session}/download', [CoacheeSessionController::class, 'download'])->name('sessions.download');
        // Ruta para acceso por token seguro (desde email)
        Route::get('sign/{token}', [CoacheeSessionController::class, 'signByToken'])->name('sessions.sign.token');
    });

// Rutas para generacion y descarga de reporte de sesion
// Rutas para generacion y descarga de reporte de sesion
Route::prefix('service')->name('service.')->middleware([
    'auth',
    'role:coachee|coach'  // El pipe (|) funciona como OR
])->group(function () {
    Route::post('pdf/summary', EvaluationPdfController::class)->name('pdf.summary');
});
require __DIR__ . '/auth.php';
