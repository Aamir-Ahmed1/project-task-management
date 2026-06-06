<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\WorkLogController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{project}/archive', [ProjectController::class, 'archive']);
    Route::get('/projects/{project}/progress', [ProjectController::class, 'progress']);

    // Tasks
    Route::apiResource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign']);
    Route::get('/tasks/{task}/timeline', [TaskController::class, 'timeline']);

    // Work Logs
    Route::get('/tasks/{task}/work-logs', [WorkLogController::class, 'index']);
    Route::post('/tasks/{task}/work-logs', [WorkLogController::class, 'store']);
    Route::get('/work-logs/{workLog}', [WorkLogController::class, 'show']);
    Route::post('/work-logs/{workLog}/replies', [WorkLogController::class, 'addReply']);
    Route::get('/work-logs/{workLog}/replies', [WorkLogController::class, 'replies']);

    // Reports
    Route::get('/reports/projects', [ReportController::class, 'projectsReport']);
    Route::get('/reports/projects/{project}', [ReportController::class, 'projectReport']);
    Route::get('/reports/employee/{user}', [ReportController::class, 'employeeReport']);

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index']);

    // Users
    Route::get('/users', [\App\Http\Controllers\Api\AuthController::class, 'users']);

    // Dashboard
    Route::get('/dashboard/admin', [DashboardController::class, 'admin']);
    Route::get('/dashboard/project-manager', [DashboardController::class, 'projectManager']);
    Route::get('/dashboard/employee', [DashboardController::class, 'employee']);
});
