<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SiteGroupController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Admin routes (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('site-groups', SiteGroupController::class)
        ->only(['index', 'store', 'update', 'destroy', 'show']);

    Route::resource('sites', SiteController::class)
        ->only(['index', 'store', 'update', 'destroy', 'show']);

    Route::resource('users', UserController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('users/{user}/permissions', [PermissionController::class, 'show'])->name('users.permissions.show');
    Route::get('users/{user}/permissions/form', [PermissionController::class, 'fragment'])->name('users.permissions.fragment');
    Route::post('users/{user}/permissions', [PermissionController::class, 'update'])->name('users.permissions.update');

    Route::get('/logs/system', [LogController::class, 'system'])->name('logs.system');
    Route::get('/logs/sync', [LogController::class, 'sync'])->name('logs.sync');
});
