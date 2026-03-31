<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard (todos autenticados)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/db-test', function () {
    return response()->json([
        'driver' => DB::connection()->getDriverName(),
        'database' => config('database.connections.pgsql.database'),
        'host' => config('database.connections.pgsql.host')
    ]);
});

Route::middleware('auth')->group(function () {
    // Perfil do usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Empresas (admin_global e admin_cliente)
    Route::middleware('role:admin_global,admin_cliente')->group(function () {
        Route::resource('companies', CompanyController::class);
    });

    // Gestão de Usuários (admin_global e admin_cliente)
    Route::middleware('role:admin_global,admin_cliente')->group(function () {
        Route::resource('users', UserManagementController::class);
    });

    // Questões (admin_global only)
    Route::middleware('role:admin_global')->group(function () {
        Route::resource('questions', QuestionController::class);
    });

    // Avaliações (todos autenticados)
    Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::post('/assessments', [AssessmentController::class, 'create'])->name('assessments.create');
    Route::get('/assessments/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');
    Route::post('/assessments/{assessment}/answer', [AssessmentController::class, 'storeAnswer'])->name('assessments.answer');
    Route::post('/assessments/{assessment}/complete', [AssessmentController::class, 'complete'])->name('assessments.complete');
    Route::get('/assessments/{assessment}/report', [AssessmentController::class, 'report'])->name('assessments.report');
});

require __DIR__ . '/auth.php';
