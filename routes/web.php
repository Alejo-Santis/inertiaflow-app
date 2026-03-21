<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Solo login — el registro público está deshabilitado
Route::middleware('guest')->group(function () {
    Route::get('login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    // Recuperación de contraseña
    Route::get('forgot-password',        [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('forgot-password',       [ForgotPasswordController::class, 'sendLink'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class,  'showForm'])->name('password.reset');
    Route::post('reset-password',        [ResetPasswordController::class,  'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('dashboard',  [DashboardController::class,  'index'])->name('dashboard');
    Route::get('analytics',  [AnalyticsController::class,  'index'])->name('analytics');

    // Proyectos
    Route::resource('projects', ProjectController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    // Tareas, miembros y comentarios dentro de un proyecto
    Route::prefix('projects/{project}')->name('projects.')->group(function () {

        // Tareas — lista, crear, detalle, kanban, cambiar estado
        Route::resource('tasks', TaskController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
        Route::get('kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');

        // Miembros del proyecto
        Route::post('members',        [ProjectMemberController::class, 'store'])->name('members.store');
        Route::delete('members/{user}', [ProjectMemberController::class, 'destroy'])->name('members.destroy');

        // Comentarios en tareas
        Route::post('tasks/{task}/comments',                [CommentController::class, 'store'])->name('tasks.comments.store');
        Route::delete('tasks/{task}/comments/{comment}',    [CommentController::class, 'destroy'])->name('tasks.comments.destroy');

        // Registro de tiempo en tareas
        Route::post('tasks/{task}/time-logs',               [TimeLogController::class, 'store'])->name('tasks.time-logs.store');
        Route::delete('tasks/{task}/time-logs/{timeLog}',   [TimeLogController::class, 'destroy'])->name('tasks.time-logs.destroy');
    });

    // Perfil del usuario autenticado
    Route::get('profile',          [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Gestión de usuarios — solo admin (el controlador verifica el rol internamente)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});
