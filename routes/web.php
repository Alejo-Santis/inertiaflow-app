<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentMemberController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\OrganizationInvitationController;
use App\Http\Controllers\MyTasksController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Página pública de invitación (token único, sin necesidad de estar logueado para verla)
Route::get('invitations/{token}', [OrganizationInvitationController::class, 'show'])->name('invitations.show');

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
    Route::get('workload',   [WorkloadController::class,   'index'])->name('workload');
    Route::get('my-tasks',   [MyTasksController::class,    'index'])->name('my-tasks');
    Route::get('search',     [SearchController::class,     'index'])->name('search');
    Route::resource('meetings', MeetingController::class)->only(['index', 'store', 'update', 'destroy']);

    // Proyectos
    Route::resource('projects', ProjectController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    // Tareas, miembros y comentarios dentro de un proyecto
    Route::prefix('projects/{project}')->name('projects.')->group(function () {

        // Tareas — lista, crear, detalle, kanban, cambiar estado, exportar
        Route::resource('tasks', TaskController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
        Route::get('kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
        Route::get('tasks-export', [TaskController::class, 'export'])->name('tasks.export');

        // Miembros del proyecto
        Route::post('members',        [ProjectMemberController::class, 'store'])->name('members.store');
        Route::delete('members/{user}', [ProjectMemberController::class, 'destroy'])->name('members.destroy');

        // Comentarios en tareas
        Route::post('tasks/{task}/comments',                [CommentController::class, 'store'])->name('tasks.comments.store');
        Route::delete('tasks/{task}/comments/{comment}',    [CommentController::class, 'destroy'])->name('tasks.comments.destroy');

        // Registro de tiempo en tareas
        Route::post('tasks/{task}/time-logs',               [TimeLogController::class, 'store'])->name('tasks.time-logs.store');
        Route::delete('tasks/{task}/time-logs/{timeLog}',   [TimeLogController::class, 'destroy'])->name('tasks.time-logs.destroy');

        // Adjuntos en tareas
        Route::post('tasks/{task}/attachments',                          [AttachmentController::class, 'store'])->name('tasks.attachments.store');
        Route::delete('tasks/{task}/attachments/{attachment}',           [AttachmentController::class, 'destroy'])->name('tasks.attachments.destroy');

        // Labels del proyecto
        Route::post('labels',           [LabelController::class, 'store'])->name('labels.store');
        Route::delete('labels/{label}', [LabelController::class, 'destroy'])->name('labels.destroy');
    });

    // Perfil del usuario autenticado
    Route::get('profile',          [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('profile/avatar',  [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    // Notificaciones
    Route::get('notifications',                              [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-all-read',              [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::patch('notifications/{notification}/read',       [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::delete('notifications/{notification}',           [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Organizaciones
    Route::resource('organizations', OrganizationController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    Route::prefix('organizations/{organization}')->name('organizations.')->group(function () {
        // Invitaciones
        Route::post('invitations',                          [OrganizationInvitationController::class, 'store'])->name('invitations.store');
        Route::post('invitations/{token}/accept',           [OrganizationInvitationController::class, 'accept'])->name('invitations.accept');
        Route::delete('invitations/{invitation}',           [OrganizationInvitationController::class, 'destroy'])->name('invitations.destroy');

        // Miembros de la organización
        Route::post('members',                         [OrganizationMemberController::class, 'store'])->name('members.store');
        Route::patch('members/{user}',                 [OrganizationMemberController::class, 'update'])->name('members.update');
        Route::delete('members/{user}',                [OrganizationMemberController::class, 'destroy'])->name('members.destroy');

        // Departamentos
        Route::post('departments',                     [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('departments/{department}',         [DepartmentController::class, 'show'])->name('departments.show');
        Route::patch('departments/{department}',       [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('departments/{department}',      [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Miembros de un departamento
        Route::post('departments/{department}/members',                [DepartmentMemberController::class, 'store'])->name('departments.members.store');
        Route::patch('departments/{department}/members/{user}',        [DepartmentMemberController::class, 'update'])->name('departments.members.update');
        Route::delete('departments/{department}/members/{user}',       [DepartmentMemberController::class, 'destroy'])->name('departments.members.destroy');
    });

    // Gestión de usuarios + audit logs — solo admin
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log');
    });
});
