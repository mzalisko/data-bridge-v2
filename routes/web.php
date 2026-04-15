<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SiteGroupController;
use App\Http\Controllers\Admin\FavoriteController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SitePhoneController;
use App\Http\Controllers\Admin\SitePriceController;
use App\Http\Controllers\Admin\SiteAddressController;
use App\Http\Controllers\Admin\SiteSocialController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\BatchController;
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

    // Batch routes must be before resource to avoid {site}='batch' conflict
    Route::get( 'sites/batch', [BatchController::class, 'show'])->name('sites.batch.show');
    Route::post('sites/batch', [BatchController::class, 'apply'])->name('sites.batch');

    Route::resource('sites', SiteController::class)
        ->only(['index', 'store', 'update', 'destroy', 'show']);

    Route::post('sites/{site}/favorite', [FavoriteController::class, 'toggle'])->name('sites.favorite');

    Route::post('sites/{site}/api-key/generate', [ApiKeyController::class, 'generate'])->name('sites.api-key.generate');
    Route::post('sites/{site}/api-key/revoke',   [ApiKeyController::class, 'revoke'])->name('sites.api-key.revoke');

    // Site data CRUD
    Route::post(  'sites/{site}/phones',              [SitePhoneController::class,   'store']  )->name('phones.store');
    Route::put(   'sites/{site}/phones/{phone}',      [SitePhoneController::class,   'update'] )->name('phones.update');
    Route::delete('sites/{site}/phones/{phone}',      [SitePhoneController::class,   'destroy'])->name('phones.destroy');

    Route::post(  'sites/{site}/prices',              [SitePriceController::class,   'store']  )->name('prices.store');
    Route::put(   'sites/{site}/prices/{price}',      [SitePriceController::class,   'update'] )->name('prices.update');
    Route::delete('sites/{site}/prices/{price}',      [SitePriceController::class,   'destroy'])->name('prices.destroy');

    Route::post(  'sites/{site}/addresses',           [SiteAddressController::class, 'store']  )->name('addresses.store');
    Route::put(   'sites/{site}/addresses/{address}', [SiteAddressController::class, 'update'] )->name('addresses.update');
    Route::delete('sites/{site}/addresses/{address}', [SiteAddressController::class, 'destroy'])->name('addresses.destroy');

    Route::post(  'sites/{site}/socials',             [SiteSocialController::class,  'store']  )->name('socials.store');
    Route::put(   'sites/{site}/socials/{social}',    [SiteSocialController::class,  'update'] )->name('socials.update');
    Route::delete('sites/{site}/socials/{social}',    [SiteSocialController::class,  'destroy'])->name('socials.destroy');

    Route::resource('users', UserController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('users/{user}/permissions', [PermissionController::class, 'show'])->name('users.permissions.show');
    Route::get('users/{user}/permissions/form', [PermissionController::class, 'fragment'])->name('users.permissions.fragment');
    Route::post('users/{user}/permissions', [PermissionController::class, 'update'])->name('users.permissions.update');

    Route::get('/logs/system', [LogController::class, 'system'])->name('logs.system');
    Route::get('/logs/sync', [LogController::class, 'sync'])->name('logs.sync');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/countries', [SettingsController::class, 'storeCountry'])->name('settings.countries.store');
    Route::delete('/settings/countries/{country}', [SettingsController::class, 'destroyCountry'])->name('settings.countries.destroy');
});
